<?php

namespace App\DTO;

/**
 * DTO representing the response returned by the search service.
 *
 * status: one of "EXACT_MATCH", "FUTURE_MATCH", "NO_MATCH", "INVALID_REQUEST"
 * rides: array of associative arrays (each is a formatted ride ready for the frontend)
 */
class RideSearchResponse
{
    /**
     * @param array<int,array<string,mixed>> $rides
     */
    public function __construct(
        public readonly string $status,
        public readonly array $rides
    ) {}
}
