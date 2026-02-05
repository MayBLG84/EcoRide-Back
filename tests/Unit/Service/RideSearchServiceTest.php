<?php

namespace App\Tests\Service;

use App\DTO\RideSearchRequest;
use App\Entity\Ride;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehicleBrand;
use App\Service\RideSearchService;
use App\Service\SecurityService;
use App\Repository\RideRepository;
use PHPUnit\Framework\TestCase;

class RideSearchServiceTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject&RideRepository */
    private RideRepository $rideRepository;
    /** @var \PHPUnit\Framework\MockObject\MockObject&SecurityService */
    private SecurityService $securityService;
    private RideSearchService $service;

    protected function setUp(): void
    {
        $this->rideRepository = $this->createMock(RideRepository::class);
        $this->securityService = $this->createMock(SecurityService::class);

        $this->service = new RideSearchService(
            $this->rideRepository,
            $this->securityService
        );
    }

    // -------------------- INPUT VALIDATION --------------------

    public function testInvalidRequestReturnsInvalidRequest(): void
    {
        $request = new RideSearchRequest(originCity: '', destinyCity: '', date: null);

        $response = $this->service->search($request);

        $this->assertEquals('INVALID_REQUEST', $response->status);
    }

    public function testInvalidCityReturnsInvalidCity(): void
    {
        $this->securityService
            ->method('isValidCity')
            ->willReturn(false);

        $request = new RideSearchRequest(originCity: '123', destinyCity: 'City', date: ['year' => 2030, 'month' => 1, 'day' => 1]);

        $response = $this->service->search($request);

        $this->assertEquals('INVALID_CITY', $response->status);
    }

    public function testInvalidDateReturnsInvalidDate(): void
    {
        $this->securityService
            ->method('isValidCity')
            ->willReturn(true);

        $this->securityService
            ->method('isValidDate')
            ->willReturn(false);

        $request = new RideSearchRequest(originCity: 'Paris', destinyCity: 'Lyon', date: ['year' => 2020, 'month' => 1, 'day' => 1]);

        $response = $this->service->search($request);

        $this->assertEquals('INVALID_DATE', $response->status);
    }

    // -------------------- NO FILTERS FLOW --------------------

    public function testExactMatchWithoutFilters(): void
    {
        $ride = $this->createRideMock();

        $this->securityService->method('isValidCity')->willReturn(true);
        $this->securityService->method('isValidDate')->willReturn(true);
        $this->securityService->method('dateStructToDateTimeImmutable')
            ->willReturn(new \DateTimeImmutable('2030-01-01'));
        $this->securityService->method('normalizeString')->willReturnArgument(0);

        $this->rideRepository->method('searchExact')
            ->willReturn([
                'results' => [$ride],
                'totalResults' => 1
            ]);

        $this->rideRepository->method('getGlobalFiltersMeta')->willReturn(['dummy' => 'meta']);

        $request = new RideSearchRequest(
            originCity: 'Paris',
            destinyCity: 'Lyon',
            date: ['year' => 2030, 'month' => 1, 'day' => 1]
        );

        $response = $this->service->search($request);

        $this->assertEquals('EXACT_MATCH', $response->status);
        $this->assertCount(1, $response->rides);
        $this->assertEquals(['dummy' => 'meta'], $response->filtersMetaGlobal);
    }

    public function testFutureMatchWithoutFilters(): void
    {
        $this->securityService->method('isValidCity')->willReturn(true);
        $this->securityService->method('isValidDate')->willReturn(true);
        $this->securityService->method('dateStructToDateTimeImmutable')
            ->willReturn(new \DateTimeImmutable('2030-01-01'));
        $this->securityService->method('normalizeString')->willReturnArgument(0);

        $this->rideRepository->method('searchExact')->willReturn(['results' => [], 'totalResults' => 0]);

        $futureRide = $this->createRideMock();
        $this->rideRepository->method('searchFuture')->willReturn([$futureRide]);

        $request = new RideSearchRequest(
            originCity: 'Paris',
            destinyCity: 'Lyon',
            date: ['year' => 2030, 'month' => 1, 'day' => 1]
        );

        $response = $this->service->search($request);

        $this->assertEquals('FUTURE_MATCH', $response->status);
        $this->assertCount(1, $response->rides);
    }

    public function testNoMatchWithoutFilters(): void
    {
        $this->securityService->method('isValidCity')->willReturn(true);
        $this->securityService->method('isValidDate')->willReturn(true);
        $this->securityService->method('dateStructToDateTimeImmutable')
            ->willReturn(new \DateTimeImmutable('2030-01-01'));
        $this->securityService->method('normalizeString')->willReturnArgument(0);

        $this->rideRepository->method('searchExact')->willReturn(['results' => [], 'totalResults' => 0]);
        $this->rideRepository->method('searchFuture')->willReturn([]);
        $this->rideRepository->method('getGlobalFiltersMeta')->willReturn([]);

        $request = new RideSearchRequest(
            originCity: 'Paris',
            destinyCity: 'Lyon',
            date: ['year' => 2030, 'month' => 1, 'day' => 1]
        );

        $response = $this->service->search($request);

        $this->assertEquals('NO_MATCH', $response->status);
        $this->assertEmpty($response->rides);
    }

    // -------------------- FILTERED SEARCH FLOW --------------------

    public function testExactMatchWithFilters(): void
    {
        $ride = $this->createRideMock();

        $this->securityService->method('isValidCity')->willReturn(true);
        $this->securityService->method('isValidDate')->willReturn(true);
        $this->securityService->method('dateStructToDateTimeImmutable')
            ->willReturn(new \DateTimeImmutable('2030-01-01'));
        $this->securityService->method('normalizeString')->willReturnArgument(0);

        $this->rideRepository->method('searchExact')->willReturn([
            'results' => [$ride],
            'totalResults' => 1
        ]);

        $this->rideRepository->method('getFilteredFiltersMeta')->willReturn(['dummyFiltered' => 'meta']);
        $this->rideRepository->method('getGlobalFiltersMeta')->willReturn(['dummyGlobal' => 'meta']);

        $request = new RideSearchRequest(
            originCity: 'Paris',
            destinyCity: 'Lyon',
            date: ['year' => 2030, 'month' => 1, 'day' => 1],
            filters: ['electricOnly' => true]
        );

        $response = $this->service->search($request);

        $this->assertEquals('EXACT_MATCH', $response->status);
        $this->assertEquals(['dummyFiltered' => 'meta'], $response->filtersMeta);
        $this->assertEquals(['dummyGlobal' => 'meta'], $response->filtersMetaGlobal);
    }

    public function testNoMatchWithFilters(): void
    {
        $this->securityService->method('isValidCity')->willReturn(true);
        $this->securityService->method('isValidDate')->willReturn(true);
        $this->securityService->method('dateStructToDateTimeImmutable')
            ->willReturn(new \DateTimeImmutable('2030-01-01'));
        $this->securityService->method('normalizeString')->willReturnArgument(0);

        $this->rideRepository->method('searchExact')->willReturn(['results' => [], 'totalResults' => 0]);
        $this->rideRepository->method('getFilteredFiltersMeta')->willReturn([]);
        $this->rideRepository->method('getGlobalFiltersMeta')->willReturn(['dummyGlobal' => 'meta']);

        $request = new RideSearchRequest(
            originCity: 'Paris',
            destinyCity: 'Lyon',
            date: ['year' => 2030, 'month' => 1, 'day' => 1],
            filters: ['electricOnly' => true]
        );

        $response = $this->service->search($request);

        $this->assertEquals('NO_MATCH', $response->status);
        $this->assertEquals([], $response->rides);
        $this->assertEquals(['dummyGlobal' => 'meta'], $response->filtersMetaGlobal);
    }

    // -------------------- HELPERS --------------------

    private function createRideMock(): Ride
    {
        $vehicleBrand = $this->createMock(VehicleBrand::class);
        $vehicleBrand->method('getBrand')->willReturn('Tesla');

        $vehicle = $this->createMock(Vehicle::class);
        $vehicle->method('getVehicleBrand')->willReturn($vehicleBrand);
        $vehicle->method('getModel')->willReturn('Model 3');
        $vehicle->method('isElectric')->willReturn(true);

        $driver = $this->createMock(User::class);
        $driver->method('getId')->willReturn(1);
        $driver->method('getNickname')->willReturn('Driver1');
        $driver->method('getPhoto')->willReturn(null);
        $driver->method('getAvgRating')->willReturn(4.5);

        $ride = $this->getMockBuilder(Ride::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getId',
                'getDriver',
                'getDepartureDate',
                'getDepartureIntendedTime',
                'getNbPlacesAvailable',
                'getOriginCity',
                'getPickPoint',
                'getDestinyCity',
                'getDropPoint',
                'getEstimatedDuration',
                'getVehicle',
                'isSmokersAllowed',
                'isAnimalsAllowed',
                'getOtherPreferences',
                'getPricePerson',
            ])
            ->getMock();

        $ride->method('getId')->willReturn(123);
        $ride->method('getDriver')->willReturn($driver);
        $ride->method('getDepartureDate')->willReturn(new \DateTime('2030-01-01'));
        $ride->method('getDepartureIntendedTime')->willReturn(new \DateTime('12:00'));
        $ride->method('getNbPlacesAvailable')->willReturn(3);
        $ride->method('getOriginCity')->willReturn('Paris');
        $ride->method('getPickPoint')->willReturn('Point A');
        $ride->method('getDestinyCity')->willReturn('Lyon');
        $ride->method('getDropPoint')->willReturn('Point B');
        $ride->method('getEstimatedDuration')->willReturn(120);
        $ride->method('getVehicle')->willReturn($vehicle);
        $ride->method('isSmokersAllowed')->willReturn(false);
        $ride->method('isAnimalsAllowed')->willReturn(true);
        $ride->method('getOtherPreferences')->willReturn('No music');
        $ride->method('getPricePerson')->willReturn(50.0);

        return $ride;
    }
}
