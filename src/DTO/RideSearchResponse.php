<?php

namespace App\DTO;

/**
 * DTO representing the response returned by the search service.
 *
 * status: one of "EXACT_MATCH", "FUTURE_MATCH", "NO_MATCH", "INVALID_REQUEST"
 * rides: array of associative arrays (each is a formatted ride ready for the frontend)
 * pagination: optional pagination metadata (only for EXACT_MATCH)
 */
class RideSearchResponse
{
    /**
     * @param array<int,array<string,mixed>> $rides
     * @param array<string,mixed>|null $pagination
     * @param int|null $totalResults
     */
    public function __construct(
        public readonly string $status,
        public readonly array $rides,
        public readonly ?array $pagination = null,
        public readonly ?int $totalResults = null
    ) {}
}
