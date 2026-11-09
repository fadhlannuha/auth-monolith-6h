<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\Catalog\Models\Author;
use Modules\Catalog\Models\Book;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Publisher;
use Modules\Circulation\Services\LoanService;
use Modules\Membership\Models\Member;
use Modules\Membership\Models\Membership;
use Modules\Membership\Models\MembershipRole;

class LibrarySeeder extends Seeder
{
    public function run(): void
    {
        if (Book::withTrashed()->exists()) {
            return;
        }

        $adminId = User::where('role', 'admin')->value('id');

        $roles = collect([
            ['name' => 'Mahasiswa', 'slug' => 'mahasiswa', 'description' => 'Anggota dari kalangan mahasiswa'],
            ['name' => 'Dosen', 'slug' => 'dosen', 'description' => 'Anggota dari kalangan dosen'],
            ['name' => 'Umum', 'slug' => 'umum', 'description' => 'Anggota masyarakat umum'],
        ])->map(fn (array $role) => MembershipRole::updateOrCreate(['slug' => $role['slug']], $role));

        $authors = Author::factory()->count(8)->create();
        $publishers = Publisher::factory()->count(5)->create();

        $categoryTree = [
            'Fiksi' => ['Novel', 'Cerpen'],
            'Teknologi' => ['Pemrograman', 'Jaringan'],
            'Sains' => ['Matematika', 'Fisika'],
            'Sejarah' => [],
        ];

        foreach ($categoryTree as $parentName => $children) {
            $parent = Category::create([
                'name' => $parentName,
                'slug' => strtolower($parentName),
                'description' => "Kategori {$parentName}",
            ]);

            foreach ($children as $childName) {
                Category::create([
                    'name' => $childName,
                    'slug' => strtolower($parentName.'-'.$childName),
                    'description' => "Sub-kategori {$childName}",
                    'parent_id' => $parent->id,
                ]);
            }
        }

        $books = Book::factory()->count(24)->create([
            'author_id' => fn () => $authors->random()->id,
            'publisher_id' => fn () => $publishers->random()->id,
        ]);

        $categories = Category::all();

        $books->each(function (Book $book) use ($categories) {
            $book->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->all(),
                ['added_at' => now()]
            );
        });

        $books->take(8)->each->update(['stock' => 10, 'low_stock_threshold' => 3]);

        $members = Member::factory()->count(12)->create()->each(function (Member $member) use ($roles) {
            $member->roles()->attach(
                $roles->random(rand(1, 2))->pluck('id')->all(),
                ['assigned_at' => now()]
            );

            Membership::create([
                'member_id' => $member->id,
                'membership_number' => 'KTA-'.strtoupper(substr(md5((string) $member->id), 0, 10)),
                'type' => fake()->randomElement(['basic', 'premium']),
                'status' => 'active',
                'started_at' => now()->subMonths(2),
                'ended_at' => now()->addMonths(10),
            ]);
        });

        $loanService = app(LoanService::class);

        Carbon::setTestNow(now()->subDays(20));

        try {
            $returnedLoan = $loanService->borrow(
                memberId: $members[0]->id,
                items: [
                    ['book_id' => $books[0]->id, 'quantity' => 1],
                    ['book_id' => $books[1]->id, 'quantity' => 2],
                ],
                loanDays: 14,
                processedBy: $adminId,
                notes: 'Peminjaman contoh untuk data laporan.',
            );
        } finally {
            Carbon::setTestNow();
        }

        $loanService->returnLoan($returnedLoan, $adminId, 'Dikembalikan setelah jatuh tempo.');

        $loanService->borrow(
            memberId: $members[1]->id,
            items: [['book_id' => $books[2]->id, 'quantity' => 1]],
            loanDays: 14,
            processedBy: $adminId,
        );

        $loanService->borrow(
            memberId: $members[2]->id,
            items: [['book_id' => $books[3]->id, 'quantity' => 2]],
            loanDays: 14,
            processedBy: $adminId,
        );

        Carbon::setTestNow(now()->subDays(20));

        try {
            $overdueLoan = $loanService->borrow(
                memberId: $members[3]->id,
                items: [['book_id' => $books[4]->id, 'quantity' => 1]],
                loanDays: 7,
                processedBy: $adminId,
                notes: 'Contoh peminjaman terlambat.',
            );
        } finally {
            Carbon::setTestNow();
        }

        foreach (range(5, 10) as $index) {
            $loanService->borrow(
                memberId: $members[$index]->id,
                items: [['book_id' => $books[$index]->id, 'quantity' => 1]],
                loanDays: 14,
                processedBy: $adminId,
            );
        }

        $loanToDelete = $loanService->borrow(
            memberId: $members[4]->id,
            items: [['book_id' => $books[5]->id, 'quantity' => 1]],
            loanDays: 14,
            processedBy: $adminId,
        );
        $loanToDelete = $loanService->returnLoan($loanToDelete, $adminId, 'Contoh data soft delete.');
        $loanToDelete->delete();

        if (isset($overdueLoan)) {
            $overdueLoan->update(['due_at' => now()->subDays(3)]);
        }

        $deletedBook = Book::factory()->create([
            'title' => 'Buku Contoh Soft Delete',
            'slug' => 'buku-contoh-soft-delete',
            'author_id' => $authors->first()->id,
            'publisher_id' => $publishers->first()->id,
        ]);
        $deletedBook->delete();

        $deletedMember = Member::factory()->create([
            'name' => 'Anggota Contoh Soft Delete',
            'email' => 'softdelete@example.com',
            'member_code' => 'MBR-DELETED',
        ]);
        Membership::create([
            'member_id' => $deletedMember->id,
            'membership_number' => 'KTA-DELETED01',
            'type' => 'basic',
            'status' => 'active',
            'started_at' => now(),
            'ended_at' => now()->addYear(),
        ]);
        $deletedMember->delete();
    }
}
