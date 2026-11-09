<?php

namespace Modules\Membership\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'member_code',
        'name',
        'email',
        'phone',
        'address',
        'status',
        'active_loans',
        'max_loans',
        'joined_at',
    ];

    protected $casts = [
        'active_loans' => 'integer',
        'max_loans' => 'integer',
        'joined_at' => 'datetime',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(MembershipRole::class, 'member_membership_role')->withPivot('assigned_at');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function (Builder $builder) use ($keyword) {
            $builder->where('name', 'like', "%{$keyword}%")
                ->orWhere('member_code', 'like', "%{$keyword}%")
                ->orWhere('email', 'like', "%{$keyword}%");
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['active', 'borrowing']);
    }

    public function scopeEligible(Builder $query): Builder
    {
        return $query->active()->whereColumn('active_loans', '<', 'max_loans');
    }

    public function hasActiveMembership(): bool
    {
        return $this->memberships()
            ->where('status', 'active')
            ->where(fn (Builder $query) => $query->whereNull('ended_at')->orWhere('ended_at', '>=', now()))
            ->exists();
    }

    protected static function newFactory(): MemberFactory
    {
        return MemberFactory::new();
    }
}
