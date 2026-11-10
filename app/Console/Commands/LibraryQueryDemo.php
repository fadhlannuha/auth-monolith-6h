<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Book;
use Modules\Circulation\Models\Loan;

class LibraryQueryDemo extends Command
{
    protected $signature = 'library:query-demo {--limit=10}';

    protected $description = 'Membandingkan jumlah query sebelum dan sesudah eager loading';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        if (Loan::query()->count() === 0) {
            $this->warn('Belum ada data peminjaman. Jalankan: php artisan migrate:fresh --seed');

            return self::FAILURE;
        }

        $loanWithoutEager = $this->measureLoans($limit, false);
        $loanWithEager = $this->measureLoans($limit, true);

        $bookWithoutEager = $this->measureBooks($limit, false);
        $bookWithEager = $this->measureBooks($limit, true);

        $this->info('Perbandingan jumlah query (limit '.$limit.' record)');
        $this->newLine();

        $this->table(
            ['Skenario', 'Tanpa Eager Loading', 'Dengan Eager Loading', 'Penurunan'],
            [
                [
                    'Daftar peminjaman + item + pengembalian',
                    $loanWithoutEager,
                    $loanWithEager,
                    $this->reduction($loanWithoutEager, $loanWithEager),
                ],
                [
                    'Daftar buku + penulis + penerbit + kategori',
                    $bookWithoutEager,
                    $bookWithEager,
                    $this->reduction($bookWithoutEager, $bookWithEager),
                ],
            ]
        );

        $this->line('Query dihitung menggunakan DB::enableQueryLog() dan DB::getQueryLog().');

        return self::SUCCESS;
    }

    private function measureLoans(int $limit, bool $eager): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $query = Loan::query()->limit($limit);

        if ($eager) {
            $query->with(['items', 'returnRecord']);
        }

        $loans = $query->get();

        foreach ($loans as $loan) {
            $loan->items->count();
            $loan->returnRecord?->days_late;
        }

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    private function measureBooks(int $limit, bool $eager): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $query = Book::query()->limit($limit);

        if ($eager) {
            $query->with(['author', 'publisher', 'categories']);
        }

        $books = $query->get();

        foreach ($books as $book) {
            $book->author?->name;
            $book->publisher?->name;
            $book->categories->count();
        }

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    private function reduction(int $before, int $after): string
    {
        if ($before === 0) {
            return '0%';
        }

        return round((1 - ($after / $before)) * 100).'%';
    }
}
