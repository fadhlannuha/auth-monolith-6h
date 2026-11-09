<?php

namespace Modules\Circulation\ValueObjects;

use Carbon\CarbonInterface;
use InvalidArgumentException;

final class Fine
{
    private float $amount;

    public function __construct(
        private readonly int $daysLate,
        private readonly float $dailyRate = 1000.0,
    ) {
        if ($daysLate < 0) {
            throw new InvalidArgumentException('Jumlah hari keterlambatan tidak boleh negatif.');
        }

        if ($dailyRate < 0) {
            throw new InvalidArgumentException('Tarif denda harian tidak boleh negatif.');
        }

        $this->amount = $daysLate * $dailyRate;
    }

    public static function fromDates(CarbonInterface $dueAt, CarbonInterface $returnedAt, float $dailyRate = 1000.0): self
    {
        $daysLate = (int) $dueAt->copy()->startOfDay()->diffInDays($returnedAt->copy()->startOfDay(), false);

        return new self(max(0, $daysLate), $dailyRate);
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getDaysLate(): int
    {
        return $this->daysLate;
    }

    public function getDailyRate(): float
    {
        return $this->dailyRate;
    }

    public function isZero(): bool
    {
        return $this->amount === 0.0;
    }

    public function add(self $other): self
    {
        return new self($this->daysLate + $other->daysLate, $this->dailyRate);
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount
            && $this->daysLate === $other->daysLate
            && $this->dailyRate === $other->dailyRate;
    }

    public function __toString(): string
    {
        return 'Rp '.number_format($this->amount, 0, ',', '.');
    }
}
