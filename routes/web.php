<?php

use App\Http\Controllers\Appraisal\AppraisalCheckinController;
use App\Http\Controllers\Appraisal\AppraisalController;
use App\Http\Controllers\Appraisal\AppraisalFormController;
use App\Http\Controllers\Appraisal\MyAppraisalController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatatablesController;
use App\Http\Controllers\Hr\PositionController;
use App\Http\Controllers\Hr\StaffController;
use App\Http\Controllers\Hr\TeamController;
use App\Http\Controllers\Kpi\KpiCategoryController;
use App\Http\Controllers\Kpi\KpiController;
use App\Http\Controllers\Kpi\KpiObjectiveController;
use App\Http\Controllers\Kpi\KpiObjectiveItemController;
use App\Http\Controllers\Kpi\KpiSettingController;
use App\Http\Controllers\Kpi\PositionTagController;
use App\Http\Controllers\Kpi\ManagePendingController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Project\ProjectTagController;
use App\Http\Controllers\Project\ProjectTaskController;
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

    // A staff member's own corner of the app. An appraisal is readable only by
    // the member it is about, and only once it has been generated - both
    // checked in MyAppraisalController, not by hiding the link.
    Route::get('/my-kpi', [StaffController::class, 'myKpi'])->name('my.kpi');
    Route::get('/my-tasks', [ProjectTaskController::class, 'mine'])->name('my.tasks');
    Route::get('/my-appraisals', [MyAppraisalController::class, 'index'])->name('my.appraisals.index');
    Route::get('/my-appraisals/{appraisal}', [MyAppraisalController::class, 'show'])->name('my.appraisals.show');

    // Moving your own work along, and looking at what is attached to it. Both
    // check ownership in the controller: an administrator may touch any task,
    // anyone else only the ones assigned to them.
    Route::patch('/project-tasks/{project_task}/status', [ProjectTaskController::class, 'updateStatus'])->name('project-tasks.status');
    Route::get('/project-tasks/{project_task}/attachments', [ProjectTaskController::class, 'attachments'])->name('project-tasks.attachments');

    /*
    |----------------------------------------------------------------------
    | Projects - open to every member
    |----------------------------------------------------------------------
    |
    | Anyone may start a project and plan the work in it. What they may not do
    | is put a tag on a task: a tag carries points, and points are what a
    | delivery score is made of, so letting people tag their own work would let
    | them set their own KPI. The tag field is dropped from any request that
    | does not come from the administrator - see StoreProjectTaskRequest.
    |
    | Cancelling and deleting stay administrator-only below: they end a project
    | and take its tasks, and with them the record a KPI was scored from.
    |
    */
    Route::resource('projects', ProjectController::class)->only(['index', 'store', 'show', 'update']);

    // Tasks are created and edited inside a project, so they travel with it.
    Route::resource('project-tasks', ProjectTaskController::class)->only(['store', 'update', 'destroy']);
    Route::delete('/project-tasks/{project_task}/attachments/{file}', [ProjectTaskController::class, 'destroyAttachment'])
        ->name('project-tasks.attachments.destroy');

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

        // Ending a project, and ending it for good. Adding and editing one is
        // open to every member, above.
        Route::post('/projects/{project}/cancel', [ProjectController::class, 'cancel'])->name('projects.cancel');
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

        // The cross-project task list. A member reads their own work at
        // my-tasks instead.
        Route::get('/project-tasks', [ProjectTaskController::class, 'index'])->name('project-tasks.index');
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

        // Project setup: what kinds of work are worth.
        Route::resource('project-tags', ProjectTagController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        // A position's Project KPI split and target, set on its KPI page.
        Route::put('/kpi/{position}/weighting', [KpiSettingController::class, 'updatePosition'])->name('kpi.weighting.update');
        // The project tags a position uses, from the same page.
        Route::post('/kpi/{position}/tags', [PositionTagController::class, 'store'])->name('kpi.tags.store');
        Route::delete('/kpi/{position}/tags/{tag}', [PositionTagController::class, 'destroy'])->name('kpi.tags.destroy');

        // Appraisal: the administrator reviewing a member's performance over a
        // period of their choosing. generate() is what hands it to the member,
        // so it is an action of its own rather than a flag on update().
        Route::resource('appraisals', AppraisalController::class)
            ->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::post('/appraisals/{appraisal}/generate', [AppraisalController::class, 'generate'])->name('appraisals.generate');
        Route::post('/appraisals/{appraisal}/reopen', [AppraisalController::class, 'reopen'])->name('appraisals.reopen');

        // The monthly check-ins held during the period, added on the appraisal's
        // own page rather than a screen of their own.
        Route::post('/appraisals/{appraisal}/checkins', [AppraisalCheckinController::class, 'store'])->name('appraisals.checkins.store');
        Route::put('/appraisals/{appraisal}/checkins/{checkin}', [AppraisalCheckinController::class, 'update'])->name('appraisals.checkins.update');
        Route::delete('/appraisals/{appraisal}/checkins/{checkin}', [AppraisalCheckinController::class, 'destroy'])->name('appraisals.checkins.destroy');

        // The form's own shape. Nothing about it is fixed in code - a company
        // sets its own parts, its own scale and its own bands.
        Route::get('/appraisal-form', [AppraisalFormController::class, 'edit'])->name('appraisal-form.edit');
        Route::put('/appraisal-form', [AppraisalFormController::class, 'update'])->name('appraisal-form.update');
        Route::post('/appraisal-form/parts', [AppraisalFormController::class, 'storeSection'])->name('appraisal-form.parts.store');
        Route::delete('/appraisal-form/parts/{section}', [AppraisalFormController::class, 'destroySection'])->name('appraisal-form.parts.destroy');
        Route::post('/appraisal-form/marks', [AppraisalFormController::class, 'storeRating'])->name('appraisal-form.marks.store');
        Route::delete('/appraisal-form/marks/{rating}', [AppraisalFormController::class, 'destroyRating'])->name('appraisal-form.marks.destroy');
        Route::post('/appraisal-form/bands', [AppraisalFormController::class, 'storeBand'])->name('appraisal-form.bands.store');
        Route::delete('/appraisal-form/bands/{band}', [AppraisalFormController::class, 'destroyBand'])->name('appraisal-form.bands.destroy');

        Route::get('/manage-pending', [ManagePendingController::class, 'index'])->name('manage-pending.index');
        Route::post('/manage-pending/{project_kpi}/approve', [ManagePendingController::class, 'approve'])->name('manage-pending.approve');
        Route::post('/manage-pending/{project_kpi}/reject', [ManagePendingController::class, 'reject'])->name('manage-pending.reject');
    });
});
