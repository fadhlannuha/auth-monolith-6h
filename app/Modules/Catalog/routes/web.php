<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalog\Controllers\AuthorController;
use Modules\Catalog\Controllers\BookController;
use Modules\Catalog\Controllers\CategoryController;
use Modules\Catalog\Controllers\PublisherController;

Route::middleware(['web', 'auth'])->prefix('catalog')->name('catalog.')->group(function () {
    Route::resource('books', BookController::class);
    Route::post('books/{book}/restore', [BookController::class, 'restore'])
        ->withTrashed()
        ->middleware('role:admin')
        ->name('books.restore');

    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('categories/{category}/restore', [CategoryController::class, 'restore'])
        ->withTrashed()
        ->middleware('role:admin')
        ->name('categories.restore');

    Route::get('authors', [AuthorController::class, 'index'])->name('authors.index');
    Route::post('authors', [AuthorController::class, 'store'])->name('authors.store');
    Route::delete('authors/{author}', [AuthorController::class, 'destroy'])->name('authors.destroy');

    Route::get('publishers', [PublisherController::class, 'index'])->name('publishers.index');
    Route::post('publishers', [PublisherController::class, 'store'])->name('publishers.store');
    Route::delete('publishers/{publisher}', [PublisherController::class, 'destroy'])->name('publishers.destroy');
});
