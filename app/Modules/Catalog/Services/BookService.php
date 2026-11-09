<?php

namespace Modules\Catalog\Services;

use Modules\Catalog\Models\Book;
use Modules\Shared\Contracts\BookServiceInterface;

class BookService implements BookServiceInterface
{
    public function getBookForLoan(int $bookId): ?array
    {
        $book = Book::query()->find($bookId);

        if (! $book) {
            return null;
        }

        return [
            'id' => $book->id,
            'title' => $book->title,
            'isbn' => $book->isbn,
            'stock' => $book->stock,
        ];
    }

    public function isStockAvailable(int $bookId, int $quantity): bool
    {
        $book = Book::query()->find($bookId);

        return $book !== null && $book->isInStock($quantity);
    }

    public function reduceStock(int $bookId, int $quantity): bool
    {
        $book = Book::query()->lockForUpdate()->find($bookId);

        if (! $book || ! $book->isInStock($quantity)) {
            return false;
        }

        $book->decrement('stock', $quantity);

        return true;
    }

    public function restoreStock(int $bookId, int $quantity): void
    {
        $book = Book::withTrashed()->find($bookId);

        if ($book) {
            $book->increment('stock', $quantity);
        }
    }

    public function getAvailableBooks(int $limit = 100): array
    {
        return Book::query()
            ->available()
            ->orderBy('title')
            ->limit($limit)
            ->get(['id', 'title', 'isbn', 'stock'])
            ->map(fn (Book $book) => [
                'id' => $book->id,
                'title' => $book->title,
                'isbn' => $book->isbn,
                'stock' => $book->stock,
            ])
            ->all();
    }
}
