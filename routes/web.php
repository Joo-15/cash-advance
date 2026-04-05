<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\ApprovalController;
use App\Http\Controllers\Web\ApprovalStepController;
use App\Http\Controllers\Web\ApprovalStepRoleController;
use App\Http\Controllers\Web\CashAdvanceController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DisbursementController;
use App\Http\Controllers\Web\FundUsageController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Routes
// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

Route::get('/', function () {
    return redirect()->route('login');
});

// Auth Middleware Group (SEMUA route yang butuh auth)
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard & Profile (All roles)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('/penggunaan-dana', FundUsageController::class);
    Route::get('/departments/options', [DepartmentController::class, 'getOptions']);

    // ============================================
    // STAFF & MANAGER & ADMIN & SUPER ADMIN
    // ============================================
    Route::middleware(['role:Super Admin,Admin,Employee'])->group(function () {
        Route::resource('pengajuan-pinjaman', CashAdvanceController::class);
    });



    // ============================================
    // MANAGER, ADMIN, SUPER ADMIN
    // ============================================
    Route::middleware(['role:Super Admin,Admin,Supervisor,Chef,Manager,General Manager,Manager Accounting,Finance'])->group(function () {
        // Approvals
        Route::resource('approvals', ApprovalController::class);
        Route::put('/approvals/{approval}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::put('/approvals/{approval}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    });
    Route::get('/approvals/{id}/detail', [ApprovalController::class, 'getDetail'])->name('approvals.detail');


    // ============================================
    // FINANCE
    // ============================================
    Route::middleware(['role:Super Admin,Admin,Finance'])->group(function () {
        // Disbursement 
        Route::resource('/pencairan-dana', DisbursementController::class);
    });

    // ============================================
    // SUPER ADMIN ONLY
    // ============================================
    Route::middleware(['role:Super Admin'])->group(function () {
        Route::resource('approval/approval-steps', ApprovalStepController::class);
        Route::resource('approval/approval-step-roles', ApprovalStepRoleController::class);
    });

    Route::middleware(['role:Super Admin,Admin,Finance'])->group(function () {
        Route::resource('users', UserController::class);
    });
});

// Auth Routes (login, register, etc.)
require __DIR__ . '/auth.php';
