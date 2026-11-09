<?php

use Illuminate\Support\Facades\Route;
use Modules\Membership\Controllers\MemberController;
use Modules\Membership\Controllers\MembershipRoleController;

Route::middleware(['web', 'auth'])->prefix('membership')->name('membership.')->group(function () {
    Route::resource('members', MemberController::class);
    Route::post('members/{member}/restore', [MemberController::class, 'restore'])
        ->withTrashed()
        ->middleware('role:admin')
        ->name('members.restore');

    Route::get('roles', [MembershipRoleController::class, 'index'])->name('roles.index');
    Route::post('roles', [MembershipRoleController::class, 'store'])->name('roles.store');
    Route::delete('roles/{role}', [MembershipRoleController::class, 'destroy'])->name('roles.destroy');
});
