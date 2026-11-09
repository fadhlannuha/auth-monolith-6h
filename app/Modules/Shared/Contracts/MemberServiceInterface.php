<?php

namespace Modules\Shared\Contracts;

interface MemberServiceInterface
{
    public function getMemberForLoan(int $memberId): ?array;

    public function ensureEligibleForLoan(int $memberId): void;

    public function markBorrowing(int $memberId): void;

    public function markReturned(int $memberId): void;

    public function getSelectableMembers(): array;
}
