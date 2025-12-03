<?php

namespace App\DTO;

/**
 * Simple DTO that carries search parameters from controller to service.
 * All fields are strings (nullable) because they come from query params.
 */
class RideSearchRequest
{
    public function __construct(
        public readonly ?string $originCity,
        public readonly ?string $destinyCity,
        public readonly ?array $date = null, // Ex: ['year' => 2025, 'month' => 12, 'day' => 25]
        public readonly ?int $page = 1
    ) {}
}
