<?php

namespace Modules\Circulation\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Circulation\ValueObjects\Fine;

class Loan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'loan_number',
        'member_id',
        'member_name',
        'member_code',
        'borrowed_at',
        'due_at',
        'returned_at',
        'status',
        'fine_amount',
        'notes',
        'processed_by',
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'due_at' => 'datetime',
        'returned_at' => 'datetime',
        'fine_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function returnRecord(): HasOne
    {
        return $this->hasOne(LoanReturn::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(BorrowingLog::class);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        if ($status === 'overdue') {
            return $query->overdue();
        }

        return $query->where('status', $status);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNull('returned_at')->where('due_at', '<', now());
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('borrowed_at', '>=', now()->subDays($days));
    }

    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function (Builder $builder) use ($keyword) {
            $builder->where('loan_number', 'like', "%{$keyword}%")
                ->orWhere('member_name', 'like', "%{$keyword}%")
                ->orWhere('member_code', 'like', "%{$keyword}%");
        });
    }

    public function isOverdue(): bool
    {
        return $this->returned_at === null && $this->due_at?->isPast();
    }

    public function lateFine(?CarbonInterface $returnedAt = null, float $dailyRate = 1000.0): Fine
    {
        return Fine::fromDates($this->due_at, $returnedAt ?? now(), $dailyRate);
    }
}
