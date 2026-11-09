<?php

namespace Modules\Membership\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\Membership\Models\Member;
use Modules\Shared\Contracts\MemberServiceInterface;
use Modules\Shared\Exceptions\MemberNotEligibleException;

class MemberService implements MemberServiceInterface
{
    public function getMemberForLoan(int $memberId): ?array
    {
        $member = Member::query()->find($memberId);

        if (! $member) {
            return null;
        }

        return [
            'id' => $member->id,
            'member_code' => $member->member_code,
            'name' => $member->name,
            'status' => $member->status,
            'active_loans' => $member->active_loans,
            'max_loans' => $member->max_loans,
        ];
    }

    public function ensureEligibleForLoan(int $memberId): void
    {
        $member = Member::query()->find($memberId);

        if (! $member) {
            throw new MemberNotEligibleException('Anggota tidak ditemukan.');
        }

        if (! in_array($member->status, ['active', 'borrowing'], true)) {
            throw new MemberNotEligibleException(
                "Anggota {$member->name} berstatus {$member->status} dan tidak dapat meminjam."
            );
        }

        if ($member->active_loans >= $member->max_loans) {
            throw new MemberNotEligibleException(
                "Anggota {$member->name} sudah mencapai batas maksimal {$member->max_loans} peminjaman aktif."
            );
        }

        if (! $member->hasActiveMembership()) {
            throw new MemberNotEligibleException(
                "Keanggotaan {$member->name} sudah tidak aktif. Silakan perpanjang keanggotaan."
            );
        }
    }

    public function markBorrowing(int $memberId): void
    {
        $member = Member::query()->lockForUpdate()->find($memberId);

        if (! $member) {
            return;
        }

        $member->increment('active_loans');
        $member->update(['status' => 'borrowing']);
    }

    public function markReturned(int $memberId): void
    {
        $member = Member::query()->lockForUpdate()->find($memberId);

        if (! $member) {
            return;
        }

        $activeLoans = max(0, $member->active_loans - 1);

        $member->update([
            'active_loans' => $activeLoans,
            'status' => $activeLoans === 0 ? 'active' : 'borrowing',
        ]);
    }

    public function getSelectableMembers(): array
    {
        return Member::query()
            ->eligible()
            ->whereHas('memberships', function (Builder $query) {
                $query->where('status', 'active')
                    ->where(fn (Builder $builder) => $builder->whereNull('ended_at')->orWhere('ended_at', '>=', now()));
            })
            ->orderBy('name')
            ->get(['id', 'member_code', 'name', 'active_loans', 'max_loans'])
            ->map(fn (Member $member) => [
                'id' => $member->id,
                'member_code' => $member->member_code,
                'name' => $member->name,
                'active_loans' => $member->active_loans,
                'max_loans' => $member->max_loans,
            ])
            ->all();
    }
}
