<?php

namespace Modules\Circulation\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\Circulation\Events\BookBorrowed;
use Modules\Circulation\Models\Loan;
use Modules\Circulation\Models\LoanItem;
use Modules\Circulation\Models\LoanReturn;
use Modules\Circulation\ValueObjects\Fine;
use Modules\Shared\Contracts\BookServiceInterface;
use Modules\Shared\Contracts\MemberServiceInterface;
use Modules\Shared\Exceptions\BookNotFoundException;
use Modules\Shared\Exceptions\DomainException;
use Modules\Shared\Exceptions\InsufficientBookStockException;
use Modules\Shared\Exceptions\MemberNotEligibleException;

class LoanService
{
    public function __construct(
        private readonly BookServiceInterface $bookService,
        private readonly MemberServiceInterface $memberService,
    ) {}

    public function borrow(
        int $memberId,
        array $items,
        int $loanDays = 14,
        ?int $processedBy = null,
        ?string $notes = null,
    ): Loan {
        if ($loanDays < 1) {
            throw new InvalidArgumentException('Durasi peminjaman minimal 1 hari.');
        }

        $normalizedItems = $this->normalizeItems($items);

        $loan = DB::transaction(function () use ($memberId, $normalizedItems, $loanDays, $processedBy, $notes) {
            $this->memberService->ensureEligibleForLoan($memberId);
            $member = $this->memberService->getMemberForLoan($memberId);

            if (! $member) {
                throw new MemberNotEligibleException('Anggota tidak ditemukan.');
            }

            $borrowedAt = now();

            $loan = Loan::create([
                'loan_number' => 'LN-'.$borrowedAt->format('Ymd').'-'.Str::upper(Str::random(6)),
                'member_id' => $member['id'],
                'member_name' => $member['name'],
                'member_code' => $member['member_code'],
                'borrowed_at' => $borrowedAt,
                'due_at' => $borrowedAt->copy()->addDays($loanDays),
                'status' => 'borrowed',
                'fine_amount' => 0,
                'notes' => $notes,
                'processed_by' => $processedBy,
            ]);

            foreach ($normalizedItems as $bookId => $quantity) {
                $book = $this->bookService->getBookForLoan($bookId);

                if (! $book) {
                    throw new BookNotFoundException("Buku dengan ID {$bookId} tidak ditemukan atau sudah dihapus.");
                }

                if (! $this->bookService->isStockAvailable($bookId, $quantity)) {
                    throw new InsufficientBookStockException($book['title'], $book['stock'], $quantity);
                }

                LoanItem::create([
                    'loan_id' => $loan->id,
                    'book_id' => $book['id'],
                    'book_title' => $book['title'],
                    'book_isbn' => $book['isbn'],
                    'quantity' => $quantity,
                ]);

                if (! $this->bookService->reduceStock($bookId, $quantity)) {
                    throw new InsufficientBookStockException($book['title'], $book['stock'], $quantity);
                }
            }

            $this->memberService->markBorrowing($memberId);

            return $loan;
        });

        event(new BookBorrowed($loan->load('items')));

        return $loan->load('items', 'returnRecord');
    }

    public function returnLoan(Loan $loan, ?int $processedBy = null, ?string $notes = null): Loan
    {
        if ($loan->returned_at !== null) {
            throw new DomainException('Peminjaman ini sudah dikembalikan.');
        }

        $returnedAt = now();

        $loan = DB::transaction(function () use ($loan, $returnedAt, $processedBy, $notes) {
            $lockedLoan = Loan::query()->lockForUpdate()->findOrFail($loan->id);

            if ($lockedLoan->returned_at !== null) {
                throw new DomainException('Peminjaman ini sudah dikembalikan.');
            }

            $fine = Fine::fromDates($lockedLoan->due_at, $returnedAt);

            $items = $lockedLoan->items()->get();

            foreach ($items as $item) {
                $this->bookService->restoreStock($item->book_id, $item->quantity);
            }

            $lockedLoan->update([
                'returned_at' => $returnedAt,
                'status' => 'returned',
                'fine_amount' => $fine->getAmount(),
            ]);

            LoanReturn::create([
                'loan_id' => $lockedLoan->id,
                'returned_at' => $returnedAt,
                'days_late' => $fine->getDaysLate(),
                'daily_rate' => $fine->getDailyRate(),
                'fine_amount' => $fine->getAmount(),
                'notes' => $notes,
                'processed_by' => $processedBy,
            ]);

            $this->memberService->markReturned($lockedLoan->member_id);

            return $lockedLoan;
        });

        return $loan->load('items', 'returnRecord');
    }

    private function normalizeItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            $bookId = (int) ($item['book_id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);

            if ($bookId < 1 || $quantity < 1) {
                throw new InvalidArgumentException('Setiap baris peminjaman harus memiliki buku dan jumlah minimal 1.');
            }

            $normalized[$bookId] = ($normalized[$bookId] ?? 0) + $quantity;
        }

        if ($normalized === []) {
            throw new InvalidArgumentException('Minimal satu buku harus dipinjam.');
        }

        return $normalized;
    }
}
