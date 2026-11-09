<?php

namespace Modules\Membership\Models;

use Database\Factories\MembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'membership_number',
        'type',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && ($this->ended_at === null || $this->ended_at->isFuture());
    }

    protected static function newFactory(): MembershipFactory
    {
        return MembershipFactory::new();
    }
}
