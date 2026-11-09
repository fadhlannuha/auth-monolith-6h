<?php

namespace Modules\Circulation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanReturn extends Model
{
    protected $fillable = [
        'loan_id',
        'returned_at',
        'days_late',
        'daily_rate',
        'fine_amount',
        'notes',
        'processed_by',
    ];

    protected $casts = [
        'returned_at' => 'datetime',
        'days_late' => 'integer',
        'daily_rate' => 'decimal:2',
        'fine_amount' => 'decimal:2',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
