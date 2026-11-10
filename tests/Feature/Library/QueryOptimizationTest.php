<?php

use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Book;
use Modules\Circulation\Models\Loan;
use Modules\Circulation\Services\LoanService;
use Modules\Membership\Models\Member;
use Modules\Membership\Models\Membership;

function libraryBorrowSample(int $count = 5): void
{
    $service = app(LoanService::class);

    foreach (range(1, $count) as $index) {
        $book = Book::factory()->create(['stock' => 5]);
        $member = Member::factory()->create();
        Membership::factory()->create(['member_id' => $member->id]);

        $service->borrow(
            memberId: $member->id,
            items: [['book_id' => $book->id, 'quantity' => 1]],
        );
    }
}

it('reduces the number of queries with eager loading', function () {
    libraryBorrowSample(5);

    DB::flushQueryLog();
    DB::enableQueryLog();

    $loans = Loan::query()->limit(5)->get();

    foreach ($loans as $loan) {
        $loan->items->count();
        $loan->returnRecord?->days_late;
    }

    $withoutEagerLoading = count(DB::getQueryLog());
    DB::disableQueryLog();

    DB::flushQueryLog();
    DB::enableQueryLog();

    $loans = Loan::query()->with(['items', 'returnRecord'])->limit(5)->get();

    foreach ($loans as $loan) {
        $loan->items->count();
        $loan->returnRecord?->days_late;
    }

    $withEagerLoading = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($withoutEagerLoading)->toBe(11)
        ->and($withEagerLoading)->toBe(3)
        ->and($withEagerLoading)->toBeLessThan($withoutEagerLoading);
});

it('eager loads books relations in catalog queries', function () {
    Book::factory()->count(5)->create();

    DB::flushQueryLog();
    DB::enableQueryLog();

    $books = Book::query()->with(['author', 'publisher', 'categories'])->limit(5)->get();

    foreach ($books as $book) {
        $book->author?->name;
        $book->publisher?->name;
        $book->categories->count();
    }

    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queryCount)->toBe(4);
});
