<?php

use App\Http\Controllers\ApprovalStepController;
use App\Http\Controllers\Web\Produks\ProdukController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\ApprovalStepController as WebApprovalStepController;
use App\Http\Controllers\Web\CashAdvanceController;
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
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Resource Routes
    // Route::resource('produk', ProdukController::class);
    Route::resource('pengajuan-pinjaman', CashAdvanceController::class);
    Route::resource('users', UserController::class);
    Route::resource('approval-steps', WebApprovalStepController::class);



    // Tambahkan route lainnya di sini...
    // Route::resource('pelanggan', PelangganController::class);
    // Route::resource('penjualan', PenjualanController::class);
});

// Auth Routes (login, register, etc.)
require __DIR__ . '/auth.php';
