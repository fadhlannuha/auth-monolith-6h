<?php

namespace Modules\Catalog\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;
use Modules\Catalog\ValueObjects\Isbn;

class IsbnRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            new Isbn((string) $value);
        } catch (InvalidArgumentException) {
            $fail('Format ISBN tidak valid. Gunakan ISBN-10 atau ISBN-13 yang benar.');
        }
    }
}
