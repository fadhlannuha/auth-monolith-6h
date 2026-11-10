<?php

use Illuminate\Support\Facades\Event;
use Modules\Catalog\Models\Book;
use Modules\Circulation\Events\BookBorrowed;
use Modules\Circulation\Models\BorrowingLog;
use Modules\Circulation\Services\LoanService;
use Modules\Membership\Models\Member;
use Modules\Membership\Models\Membership;

it('dispatches the BookBorrowed domain event after a successful loan', function () {
    Event::fake([BookBorrowed::class]);

    $book = Book::factory()->create(['stock' => 5]);
    $member = Member::factory()->create();
    Membership::factory()->create(['member_id' => $member->id]);

    app(LoanService::class)->borrow(
        memberId: $member->id,
        items: [['book_id' => $book->id, 'quantity' => 1]],
    );

    Event::assertDispatched(BookBorrowed::class, function (BookBorrowed $event) use ($member) {
        return $event->loan->member_id === $member->id
            && $event->loan->items->count() === 1;
    });
});

it('writes a borrowing log through the event listener', function () {
    $book = Book::factory()->create(['stock' => 5]);
    $member = Member::factory()->create();
    Membership::factory()->create(['member_id' => $member->id]);

    $loan = app(LoanService::class)->borrow(
        memberId: $member->id,
        items: [['book_id' => $book->id, 'quantity' => 2]],
    );

    $log = BorrowingLog::first();

    expect(BorrowingLog::count())->toBe(1)
        ->and($log->loan_id)->toBe($loan->id)
        ->and($log->message)->toContain($member->name)
        ->and($log->context['items'][0]['title'])->toBe($book->title)
        ->and($log->context['items'][0]['quantity'])->toBe(2);
});
