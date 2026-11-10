<?php

use Modules\Catalog\Models\Book;
use Modules\Circulation\Models\BorrowingLog;
use Modules\Circulation\Models\Loan;
use Modules\Circulation\Models\LoanItem;
use Modules\Circulation\Services\LoanService;
use Modules\Membership\Models\Member;
use Modules\Membership\Models\Membership;
use Modules\Shared\Exceptions\DomainException;
use Modules\Shared\Exceptions\InsufficientBookStockException;
use Modules\Shared\Exceptions\MemberNotEligibleException;

function libraryMember(): Member
{
    $member = Member::factory()->create();

    Membership::factory()->create([
        'member_id' => $member->id,
        'status' => 'active',
        'started_at' => now()->subMonth(),
        'ended_at' => now()->addYear(),
    ]);

    return $member;
}

it('creates a loan atomically and updates stock and member status', function () {
    $book = Book::factory()->create(['stock' => 5]);
    $member = libraryMember();

    $loan = app(LoanService::class)->borrow(
        memberId: $member->id,
        items: [['book_id' => $book->id, 'quantity' => 2]],
        loanDays: 14,
    );

    expect($loan->items)->toHaveCount(1)
        ->and($loan->items->first()->book_title)->toBe($book->title)
        ->and($loan->items->first()->book_isbn)->toBe($book->isbn)
        ->and($loan->items->first()->quantity)->toBe(2)
        ->and($loan->status)->toBe('borrowed')
        ->and($loan->due_at->greaterThan($loan->borrowed_at))->toBeTrue()
        ->and($book->fresh()->stock)->toBe(3)
        ->and($member->fresh()->active_loans)->toBe(1)
        ->and($member->fresh()->status)->toBe('borrowing')
        ->and($loan->member_name)->toBe($member->name)
        ->and($loan->member_code)->toBe($member->member_code);
});

it('merges duplicate book rows into a single loan item', function () {
    $book = Book::factory()->create(['stock' => 5]);
    $member = libraryMember();

    $loan = app(LoanService::class)->borrow(
        memberId: $member->id,
        items: [
            ['book_id' => $book->id, 'quantity' => 1],
            ['book_id' => $book->id, 'quantity' => 2],
        ],
    );

    expect($loan->items)->toHaveCount(1)
        ->and($loan->items->first()->quantity)->toBe(3)
        ->and($book->fresh()->stock)->toBe(2);
});

it('rolls back the whole transaction when stock is insufficient', function () {
    $book = Book::factory()->create(['stock' => 1]);
    $member = libraryMember();

    expect(fn () => app(LoanService::class)->borrow(
        memberId: $member->id,
        items: [['book_id' => $book->id, 'quantity' => 2]],
    ))->toThrow(InsufficientBookStockException::class);

    expect(Loan::count())->toBe(0)
        ->and(LoanItem::count())->toBe(0)
        ->and(BorrowingLog::count())->toBe(0)
        ->and($book->fresh()->stock)->toBe(1)
        ->and($member->fresh()->active_loans)->toBe(0)
        ->and($member->fresh()->status)->toBe('active');
});

it('rolls back when the member is not eligible', function () {
    $book = Book::factory()->create(['stock' => 5]);
    $member = Member::factory()->suspended()->create();
    Membership::factory()->create(['member_id' => $member->id]);

    expect(fn () => app(LoanService::class)->borrow(
        memberId: $member->id,
        items: [['book_id' => $book->id, 'quantity' => 1]],
    ))->toThrow(MemberNotEligibleException::class);

    expect(Loan::count())->toBe(0)
        ->and($book->fresh()->stock)->toBe(5);
});

it('returns books and calculates the fine automatically', function () {
    $book = Book::factory()->create(['stock' => 5]);
    $member = libraryMember();
    $service = app(LoanService::class);

    $loan = $service->borrow(
        memberId: $member->id,
        items: [['book_id' => $book->id, 'quantity' => 2]],
    );

    $loan->update(['due_at' => now()->subDays(3)]);

    $returned = $service->returnLoan($loan, notes: 'Terlambat tiga hari.');

    expect($returned->returned_at)->not->toBeNull()
        ->and($returned->status)->toBe('returned')
        ->and((float) $returned->fine_amount)->toBe(3000.0)
        ->and($returned->returnRecord->days_late)->toBe(3)
        ->and((float) $returned->returnRecord->fine_amount)->toBe(3000.0)
        ->and($book->fresh()->stock)->toBe(5)
        ->and($member->fresh()->active_loans)->toBe(0)
        ->and($member->fresh()->status)->toBe('active');
});

it('rejects returning the same loan twice', function () {
    $book = Book::factory()->create(['stock' => 5]);
    $member = libraryMember();
    $service = app(LoanService::class);

    $loan = $service->borrow(
        memberId: $member->id,
        items: [['book_id' => $book->id, 'quantity' => 1]],
    );

    $service->returnLoan($loan);

    expect(fn () => $service->returnLoan($loan->fresh()))
        ->toThrow(DomainException::class);

    expect($book->fresh()->stock)->toBe(5)
        ->and($member->fresh()->active_loans)->toBe(0);
});
