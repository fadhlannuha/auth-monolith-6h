<?php

use App\Models\User;
use Modules\Catalog\Models\Book;
use Modules\Circulation\Models\Loan;
use Modules\Membership\Models\Member;

it('soft deletes and restores a book', function () {
    $book = Book::factory()->create();

    $book->delete();

    expect(Book::count())->toBe(0)
        ->and(Book::withTrashed()->count())->toBe(1)
        ->and(Book::onlyTrashed()->first()->id)->toBe($book->id);

    $book->restore();

    expect(Book::count())->toBe(1);
});

it('soft deletes and restores a member', function () {
    $member = Member::factory()->create();

    $member->delete();

    expect(Member::count())->toBe(0)
        ->and(Member::withTrashed()->count())->toBe(1);

    $member->restore();

    expect(Member::count())->toBe(1);
});

it('soft deletes and restores a loan', function () {
    $loan = Loan::create([
        'loan_number' => 'LN-TEST-001',
        'member_id' => Member::factory()->create()->id,
        'member_name' => 'Anggota Uji',
        'member_code' => 'MBR-TEST',
        'borrowed_at' => now(),
        'due_at' => now()->addDays(7),
        'status' => 'borrowed',
        'fine_amount' => 0,
    ]);

    $loan->delete();

    expect(Loan::count())->toBe(0)
        ->and(Loan::withTrashed()->count())->toBe(1);

    $loan->restore();

    expect(Loan::count())->toBe(1);
});

it('allows an admin to restore a soft deleted book through the route', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $book = Book::factory()->create();
    $book->delete();

    $this->actingAs($admin)
        ->post(route('catalog.books.restore', $book))
        ->assertRedirect(route('catalog.books.index'));

    expect(Book::whereKey($book->id)->exists())->toBeTrue();

    $this->actingAs($admin)
        ->get(route('catalog.books.index', ['trashed' => 1]))
        ->assertOk();
});

it('forbids a regular user from restoring data', function () {
    $user = User::factory()->create(['role' => 'user']);
    $book = Book::factory()->create();
    $book->delete();

    $this->actingAs($user)
        ->post(route('catalog.books.restore', $book))
        ->assertForbidden();

    expect(Book::whereKey($book->id)->exists())->toBeFalse();
});
