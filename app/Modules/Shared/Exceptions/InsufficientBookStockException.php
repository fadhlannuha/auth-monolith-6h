<?php

namespace Modules\Shared\Exceptions;

class InsufficientBookStockException extends DomainException
{
    public function __construct(
        public readonly string $bookTitle,
        public readonly int $available,
        public readonly int $requested,
    ) {
        parent::__construct(
            "Stok buku \"{$bookTitle}\" tidak mencukupi. Tersedia {$available}, diminta {$requested}."
        );
    }
}
