<?php

use Illuminate\Support\Facades\Route;
use Modules\Circulation\Controllers\LoanController;

Route::middleware(['web', 'auth'])->prefix('circulation')->name('circulation.')->group(function () {
    Route::get('loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('loans/create', [LoanController::class, 'create'])->name('loans.create');
    Route::post('loans', [LoanController::class, 'store'])->name('loans.store');
    Route::get('loans/{loan}', [LoanController::class, 'show'])->name('loans.show');
    Route::post('loans/{loan}/return', [LoanController::class, 'returnLoan'])->name('loans.return');
    Route::post('loans/{loan}/restore', [LoanController::class, 'restore'])
        ->withTrashed()
        ->middleware('role:admin')
        ->name('loans.restore');
});
