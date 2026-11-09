<?php

namespace Modules\Circulation\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Circulation\Events\BookBorrowed;
use Modules\Circulation\Models\BorrowingLog;

class RecordBookBorrowed
{
    public function handle(BookBorrowed $event): void
    {
        $loan = $event->loan;
        $loan->loadMissing('items');

        $message = "Anggota {$loan->member_name} ({$loan->member_code}) meminjam "
            .$loan->items->sum('quantity')." buku melalui {$loan->loan_number}.";

        BorrowingLog::create([
            'loan_id' => $loan->id,
            'message' => $message,
            'context' => [
                'member_id' => $loan->member_id,
                'member_code' => $loan->member_code,
                'loan_number' => $loan->loan_number,
                'items' => $loan->items->map(fn ($item) => [
                    'book_id' => $item->book_id,
                    'title' => $item->book_title,
                    'isbn' => $item->book_isbn,
                    'quantity' => $item->quantity,
                ])->all(),
            ],
        ]);

        Log::info('[BookBorrowed] '.$message);
    }
}
