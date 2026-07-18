<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        string $message = 'Insufficient stock.',
        private readonly int $available = 0,
        private readonly int $requested = 0,
    ) {
        parent::__construct($message);
    }

    public function getAvailable(): int
    {
        return $this->available;
    }

    public function getRequested(): int
    {
        return $this->requested;
    }
}
