<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatatablesController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\KpiObjectiveController;
use App\Http\Controllers\ManagePendingController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectPhaseController;
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

Route::middleware(['auth', 'admin.position'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/change-password', [ChangePasswordController::class, 'show'])->name('password.change');
    Route::post('/change-password', [ChangePasswordController::class, 'update'])->name('password.change.update');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::post('/datatables/listing', [DatatablesController::class, 'listing'])->name('datatables.listing');

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

    Route::resource('projects', ProjectController::class)->except(['show']);
    Route::post('/projects/{project}/cancel', [ProjectController::class, 'cancel'])->name('projects.cancel');

    Route::resource('project-phases', ProjectPhaseController::class)->except(['show']);
    Route::post('/project-phases/{project_phase}/approve', [ProjectPhaseController::class, 'approve'])->name('project-phases.approve');
    Route::post('/project-phases/{project_phase}/reject', [ProjectPhaseController::class, 'reject'])->name('project-phases.reject');
    Route::get('/project-phases/{project_phase}/attachments', [ProjectPhaseController::class, 'attachments'])->name('project-phases.attachments');

    Route::resource('kpi', KpiController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('kpi.objectives', KpiObjectiveController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::get('/manage-pending', [ManagePendingController::class, 'index'])->name('manage-pending.index');
    Route::post('/manage-pending/{project_kpi}/approve', [ManagePendingController::class, 'approve'])->name('manage-pending.approve');
    Route::post('/manage-pending/{project_kpi}/reject', [ManagePendingController::class, 'reject'])->name('manage-pending.reject');
});
