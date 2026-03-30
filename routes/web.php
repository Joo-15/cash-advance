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
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Auth Middleware Group (SEMUA route yang butuh auth)
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Resource Routes
    // Route::resource('produk', ProdukController::class);
    Route::resource('pengajuan-pinjaman', CashAdvanceController::class);
    Route::resource('approvals', ApprovalController::class);
    Route::get('/approvals/{id}/detail', [ApprovalController::class, 'getDetail'])->name('approvals.detail');
    Route::put('/approvals/{approval}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::put('/approvals/{approval}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    Route::resource('users', UserController::class);
    Route::resource('approval/approval-steps', ApprovalStepController::class);
    Route::resource('approval/approval-step-roles', ApprovalStepRoleController::class);

    // DISBURSEMENT
    Route::resource('/pencairan-dana', DisbursementController::class);
    Route::resource('/penggunaan-dana', FundUsageController::class);
    // Route::get('/disbursement/{disbursement}/show', [DisbursementController::class, 'show'])->name('disbursement.show');
    // Route::get('/disbursement', [DisbursementController::class, 'store'])->name('disbursement.store');
    Route::get('/departments/options', [DepartmentController::class, 'getOptions']);





    // Tambahkan route lainnya di sini...
    // Route::resource('pelanggan', PelangganController::class);
    // Route::resource('penjualan', PenjualanController::class);
});

// Auth Routes (login, register, etc.)
require __DIR__ . '/auth.php';
