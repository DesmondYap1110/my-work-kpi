<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatatablesController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\KpiSettingController;
use App\Http\Controllers\KpiCategoryController;
use App\Http\Controllers\KpiObjectiveController;
use App\Http\Controllers\KpiObjectiveItemController;
use App\Http\Controllers\ManagePendingController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectTaskController;
use App\Http\Controllers\ProjectTagController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'update'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Signed in
|--------------------------------------------------------------------------
|
| Open to every active staff member. Keep this outer group to things a person
| may do about their own work: their KPI, their tasks, their password. Anything
| that configures the company belongs in the administrator group below.
|
*/
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/change-password', [ChangePasswordController::class, 'show'])->name('password.change');
    Route::post('/change-password', [ChangePasswordController::class, 'update'])->name('password.change.update');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // The list tables all come through here, so this endpoint has to make the
    // same distinction the routes do - see DatatablesController.
    Route::post('/datatables/listing', [DatatablesController::class, 'listing'])->name('datatables.listing');

    // A staff member's own corner of the app.
    Route::get('/my-kpi', [StaffController::class, 'myKpi'])->name('my.kpi');
    Route::get('/my-tasks', [ProjectTaskController::class, 'mine'])->name('my.tasks');

    // Moving your own work along, and looking at what is attached to it. Both
    // check ownership in the controller: an administrator may touch any task,
    // anyone else only the ones assigned to them.
    Route::patch('/project-tasks/{project_task}/status', [ProjectTaskController::class, 'updateStatus'])->name('project-tasks.status');
    Route::get('/project-tasks/{project_task}/attachments', [ProjectTaskController::class, 'attachments'])->name('project-tasks.attachments');

    /*
    |----------------------------------------------------------------------
    | Administrator only
    |----------------------------------------------------------------------
    |
    | Teams, positions, members, projects, tags, KPIs and the weighting
    | between them: the shape of the company, which only the system
    | administrator sets. Hiding these in the sidebar is presentation only -
    | this group is what actually refuses them.
    |
    */
    Route::middleware('admin')->group(function () {
        Route::resource('positions', PositionController::class)
            ->parameters(['positions' => 'position'])
            ->only(['index', 'store', 'update', 'destroy']);

        Route::resource('teams', TeamController::class)
            ->parameters(['teams' => 'team'])
            ->only(['index', 'store', 'update', 'destroy']);
        Route::patch('/teams/{team}/toggle-status', [TeamController::class, 'toggleStatus'])->name('teams.toggle-status');

        Route::resource('staff', StaffController::class)
            ->parameters(['staff' => 'staff'])
            ->except(['show']);
        Route::patch('/staff/{staff}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');
        Route::get('/staff/{staff}/kpi', [StaffController::class, 'viewKpi'])->name('staff.view-kpi');
        Route::post('/staff/check-unique', [StaffController::class, 'checkUnique'])->name('staff.check-unique');

        // show() is the project workspace: its tasks, added and edited in place.
        // No create screen - a project is made in a modal on the list.
        Route::resource('projects', ProjectController::class)->except(['create']);
        Route::post('/projects/{project}/cancel', [ProjectController::class, 'cancel'])->name('projects.cancel');

        // Tasks are created from inside a project, so there is no create/edit
        // screen - only the cross-project list at index. Changing a task's
        // status and reading its attachments sit outside this group, since
        // the person doing the work needs both.
        Route::resource('project-tasks', ProjectTaskController::class)
            ->only(['index', 'store', 'update', 'destroy']);
        Route::post('/project-tasks/{project_task}/approve', [ProjectTaskController::class, 'approve'])->name('project-tasks.approve');
        Route::post('/project-tasks/{project_task}/reject', [ProjectTaskController::class, 'reject'])->name('project-tasks.reject');

        Route::resource('kpi', KpiController::class)->parameters(['kpi' => 'position'])->only(['store', 'destroy']);
        // Keyed by position: a KPI is just a position's objectives.
        Route::resource('kpi.objectives', KpiObjectiveController::class)
            ->parameters(['kpi' => 'position'])
            ->only(['index', 'store', 'update', 'destroy']);

        // Categories belong to a position; the position is posted with the form.
        Route::resource('kpi-categories', KpiCategoryController::class)
            ->parameters(['kpi-categories' => 'category'])
            ->only(['store', 'update', 'destroy']);

        // The scored items under one objective.
        Route::resource('kpi.objectives.items', KpiObjectiveItemController::class)
            ->parameters(['kpi' => 'position', 'items' => 'item'])
            ->only(['index', 'store', 'update', 'destroy']);

        // Project setup: what kinds of work are worth, and how much of a KPI
        // score comes from delivering them.
        Route::resource('project-tags', ProjectTagController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::get('/kpi-weighting', [KpiSettingController::class, 'edit'])->name('kpi-settings.edit');
        Route::put('/kpi-weighting', [KpiSettingController::class, 'update'])->name('kpi-settings.update');

        Route::get('/manage-pending', [ManagePendingController::class, 'index'])->name('manage-pending.index');
        Route::post('/manage-pending/{project_kpi}/approve', [ManagePendingController::class, 'approve'])->name('manage-pending.approve');
        Route::post('/manage-pending/{project_kpi}/reject', [ManagePendingController::class, 'reject'])->name('manage-pending.reject');
    });
});
