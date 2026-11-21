<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BankOverviewController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureBankAdministrator;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('index');
Route::view('/about_me', 'about_me')->name('about_me');
Route::view('/contact_as', 'contact_as')->name('contact_as');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [BankOverviewController::class, 'dashboard'])
        ->middleware('verified')
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'bank.staff'])->group(function () {
    Route::get('/reports', [BankOverviewController::class, 'reports'])->name('reports');
    Route::get('/reports/pdf', [BankOverviewController::class, 'reportPdf'])->name('reports.pdf');
    Route::get('/reports/excel', [BankOverviewController::class, 'reportExcel'])->name('reports.excel');
    Route::get('/search', [BankOverviewController::class, 'search'])->name('bank.search');
    Route::resource('accounts', AccountController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('transactions', TransactionController::class);
    Route::resource('loans', LoanController::class);
});

Route::resource('users', UserController::class)->middleware(['auth', EnsureBankAdministrator::class]);

require __DIR__.'/auth.php';
