<?php

namespace Modules\Catalog\ValueObjects;

use InvalidArgumentException;

final class Isbn
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(preg_replace('/[^0-9Xx]/', '', $value) ?? '');

        if (! self::isValid($normalized)) {
            throw new InvalidArgumentException("Format ISBN tidak valid: {$value}");
        }

        $this->value = $normalized;
    }

    public static function isValid(string $value): bool
    {
        if (preg_match('/^\d{9}[\dX]$/', $value) === 1) {
            return self::isValidIsbn10($value);
        }

        if (preg_match('/^\d{13}$/', $value) === 1) {
            return self::isValidIsbn13($value);
        }

        return false;
    }

    public static function generate(): self
    {
        $base = '978'.str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $base[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return new self($base.$checkDigit);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private static function isValidIsbn10(string $isbn): bool
    {
        $sum = 0;

        for ($i = 0; $i < 10; $i++) {
            $digit = $isbn[$i] === 'X' ? 10 : (int) $isbn[$i];
            $sum += (10 - $i) * $digit;
        }

        return $sum % 11 === 0;
    }

    private static function isValidIsbn13(string $isbn): bool
    {
        $sum = 0;

        for ($i = 0; $i < 13; $i++) {
            $sum += (int) $isbn[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        return $sum % 10 === 0;
    }
}
