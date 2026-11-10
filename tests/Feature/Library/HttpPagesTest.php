<?php

use App\Models\User;
use Modules\Catalog\Models\Book;
use Modules\Catalog\Models\Category;
use Modules\Circulation\Services\LoanService;
use Modules\Membership\Models\Member;
use Modules\Membership\Models\Membership;
use Modules\Membership\Models\MembershipRole;

it('renders all library pages', function () {
    $user = User::factory()->create();
    $member = Member::factory()->create();
    Membership::factory()->create(['member_id' => $member->id]);
    $book = Book::factory()->create(['stock' => 5]);
    $category = Category::factory()->create();
    $book->categories()->attach($category->id, ['added_at' => now()]);
    MembershipRole::create(['name' => 'Mahasiswa', 'slug' => 'mahasiswa']);

    $loan = app(LoanService::class)->borrow(
        memberId: $member->id,
        items: [['book_id' => $book->id, 'quantity' => 1]],
    );

    $pages = [
        route('dashboard'),
        route('catalog.books.index'),
        route('catalog.books.create'),
        route('catalog.books.show', $book),
        route('catalog.books.edit', $book),
        route('catalog.categories.index'),
        route('catalog.authors.index'),
        route('catalog.publishers.index'),
        route('membership.members.index'),
        route('membership.members.create'),
        route('membership.members.show', $member),
        route('membership.members.edit', $member),
        route('membership.roles.index'),
        route('circulation.loans.index'),
        route('circulation.loans.create'),
        route('circulation.loans.show', $loan),
    ];

    $this->actingAs($user);

    foreach ($pages as $url) {
        $this->get($url)->assertOk();
    }
});

it('creates a loan through the http endpoint', function () {
    $user = User::factory()->create();
    $member = Member::factory()->create();
    Membership::factory()->create(['member_id' => $member->id]);
    $book = Book::factory()->create(['stock' => 5]);

    $response = $this->actingAs($user)->post(route('circulation.loans.store'), [
        'member_id' => $member->id,
        'loan_days' => 7,
        'items' => [
            ['book_id' => $book->id, 'quantity' => 2],
        ],
    ]);

    $response->assertRedirect();

    expect($book->fresh()->stock)->toBe(3)
        ->and($member->fresh()->active_loans)->toBe(1);
});

it('shows a validation error when borrowing exceeds stock', function () {
    $user = User::factory()->create();
    $member = Member::factory()->create();
    Membership::factory()->create(['member_id' => $member->id]);
    $book = Book::factory()->create(['stock' => 1]);

    $response = $this->actingAs($user)
        ->from(route('circulation.loans.create'))
        ->post(route('circulation.loans.store'), [
            'member_id' => $member->id,
            'loan_days' => 7,
            'items' => [
                ['book_id' => $book->id, 'quantity' => 2],
            ],
        ]);

    $response->assertRedirect(route('circulation.loans.create'))
        ->assertSessionHasErrors('loan');

    expect($book->fresh()->stock)->toBe(1)
        ->and($member->fresh()->active_loans)->toBe(0);
});
