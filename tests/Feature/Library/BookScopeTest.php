<?php

use Modules\Catalog\Models\Book;
use Modules\Catalog\Models\Category;

it('filters available books', function () {
    $available = Book::factory()->create(['stock' => 3, 'is_active' => true]);
    Book::factory()->emptyStock()->create();
    Book::factory()->create(['stock' => 2, 'is_active' => false]);

    expect(Book::available()->pluck('id')->all())->toBe([$available->id]);
});

it('searches books by title or isbn', function () {
    $book = Book::factory()->create(['title' => 'Clean Architecture']);

    expect(Book::search('Clean')->pluck('id')->all())->toBe([$book->id])
        ->and(Book::search($book->isbn)->pluck('id')->all())->toBe([$book->id]);
});

it('filters books by parent category including its children', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->create(['parent_id' => $parent->id]);
    $book = Book::factory()->create();
    $book->categories()->attach($child->id, ['added_at' => now()]);

    expect(Book::inCategory($parent->id)->pluck('id')->all())->toBe([$book->id]);
});

it('filters low stock books using a column comparison', function () {
    $low = Book::factory()->create(['stock' => 2, 'low_stock_threshold' => 3]);
    Book::factory()->create(['stock' => 10, 'low_stock_threshold' => 3]);

    expect(Book::lowStock()->pluck('id')->all())->toBe([$low->id]);
});
