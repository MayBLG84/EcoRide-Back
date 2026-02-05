<?php

namespace App\Tests\DTO;

use App\DTO\RideSearchRequest;
use PHPUnit\Framework\TestCase;

class RideSearchRequestTest extends TestCase
{
    public function testCanConstructWithAllFields(): void
    {
        $request = new RideSearchRequest(
            originCity: 'Paris',
            destinyCity: 'Lyon',
            date: ['year' => 2025, 'month' => 12, 'day' => 25],
            page: 2,
            filters: [
                'electricOnly' => true,
                'priceMin' => 10,
                'priceMax' => 50
            ],
            orderBy: 'price'
        );

        $this->assertSame('Paris', $request->originCity);
        $this->assertSame('Lyon', $request->destinyCity);
        $this->assertSame(['year' => 2025, 'month' => 12, 'day' => 25], $request->date);
        $this->assertSame(2, $request->page);
        $this->assertIsArray($request->filters);
        $this->assertSame('price', $request->orderBy);
    }

    public function testCanConstructWithMinimalFields(): void
    {
        $request = new RideSearchRequest(
            originCity: null,
            destinyCity: null
        );

        $this->assertNull($request->originCity);
        $this->assertNull($request->destinyCity);
        $this->assertNull($request->date);
        $this->assertSame(1, $request->page);
        $this->assertNull($request->filters);
        $this->assertNull($request->orderBy);
    }
}
