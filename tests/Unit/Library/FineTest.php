<?php

use Carbon\Carbon;
use Modules\Circulation\ValueObjects\Fine;

it('returns zero fine when returned on the due date', function () {
    $fine = Fine::fromDates(Carbon::parse('2026-01-10 08:00'), Carbon::parse('2026-01-10 20:00'));

    expect($fine->getDaysLate())->toBe(0)
        ->and($fine->getAmount())->toBe(0.0)
        ->and($fine->isZero())->toBeTrue();
});

it('returns zero fine when returned before the due date', function () {
    $fine = Fine::fromDates(Carbon::parse('2026-01-10'), Carbon::parse('2026-01-08'));

    expect($fine->getAmount())->toBe(0.0);
});

it('calculates fine automatically based on late days', function () {
    $fine = Fine::fromDates(Carbon::parse('2026-01-10'), Carbon::parse('2026-01-13'));

    expect($fine->getDaysLate())->toBe(3)
        ->and($fine->getAmount())->toBe(3000.0)
        ->and($fine->getDailyRate())->toBe(1000.0);
});

it('supports a custom daily rate', function () {
    $fine = new Fine(7, 2000);

    expect($fine->getAmount())->toBe(14000.0)
        ->and((string) $fine)->toBe('Rp 14.000');
});

it('rejects a negative number of late days', function () {
    new Fine(-1);
})->throws(InvalidArgumentException::class);
