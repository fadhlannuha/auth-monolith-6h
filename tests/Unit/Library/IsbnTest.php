<?php

use Modules\Catalog\ValueObjects\Isbn;

it('accepts a valid isbn 10 and normalizes it', function () {
    $isbn = new Isbn('0-8044-2957-X');

    expect($isbn->getValue())->toBe('080442957X')
        ->and((string) $isbn)->toBe('080442957X');
});

it('accepts a valid isbn 13 and removes separators', function () {
    $isbn = new Isbn('978-3-16-148410-0');

    expect($isbn->getValue())->toBe('9783161484100');
});

it('rejects an isbn with invalid checksum', function () {
    new Isbn('9783161484101');
})->throws(InvalidArgumentException::class);

it('rejects a malformed isbn', function () {
    new Isbn('BUKU-123');
})->throws(InvalidArgumentException::class);

it('generates a valid isbn 13', function () {
    $isbn = Isbn::generate();

    expect(Isbn::isValid($isbn->getValue()))->toBeTrue()
        ->and(strlen($isbn->getValue()))->toBe(13);
});

it('compares two isbn value objects', function () {
    expect((new Isbn('9783161484100'))->equals(new Isbn('978-3-16-148410-0')))->toBeTrue();
});
