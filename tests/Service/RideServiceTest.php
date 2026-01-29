<?php

namespace App\Tests\Service;

use App\Entity\Ride;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Service\RideService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\ArrayCollection;

class RideServiceTest extends TestCase
{
    private RideService $rideService;
    private $em;

    protected function setUp(): void
    {
        // Mock do EntityManager
        $this->em = $this->createMock(EntityManagerInterface::class);

        $this->rideService = new RideService($this->em);
    }

    // -------------------- CREATE RIDE --------------------

    public function testCreateRideSuccess(): void
    {
        $vehicle = new Vehicle();
        $driver = $this->createMock(User::class);
        $driver->method('getVehicles')->willReturn(new ArrayCollection([$vehicle]));

        $data = [
            'originCity' => 'Paris',
            'destinyCity' => 'Lyon',
            'vehicle' => $vehicle,
            'nbPlacesOffered' => 3,
            'pricePerson' => 50.0
        ];

        $ride = $this->rideService->createRide($driver, $data);

        $this->assertInstanceOf(Ride::class, $ride);
        $this->assertEquals('Paris', $ride->getOriginCity());
        $this->assertEquals('Lyon', $ride->getDestinyCity());
        $this->assertSame($vehicle, $ride->getVehicle());
        $this->assertSame($driver, $ride->getDriver());
    }

    public function testCreateRideVehicleNotOwnedThrowsException(): void
    {
        $vehicle = new Vehicle();
        $driver = $this->createMock(User::class);
        $driver->method('getVehicles')->willReturn(new ArrayCollection()); // driver doesn't own it

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le véhicule doit appartenir au conducteur du trajet.');

        $this->rideService->createRide($driver, [
            'originCity' => 'Paris',
            'destinyCity' => 'Lyon',
            'vehicle' => $vehicle,
            'nbPlacesOffered' => 3,
            'pricePerson' => 50.0
        ]);
    }

    // -------------------- ADD PASSENGER --------------------

    public function testAddPassengerSuccess(): void
    {
        $ride = $this->createRideWithSeats(3);
        $user = $this->createMock(User::class);
        $user->method('getCredit')->willReturn(100.0);

        $result = $this->rideService->addPassenger($ride, $user);

        $this->assertTrue($result);
        $this->assertTrue($ride->getPassengers()->contains($user));
    }

    public function testAddPassengerAlreadyInRideReturnsFalse(): void
    {
        $ride = $this->createRideWithSeats(3);
        $user = $this->createMock(User::class);
        $user->method('getCredit')->willReturn(100.0);

        // add once
        $ride->addPassenger($user);

        $result = $this->rideService->addPassenger($ride, $user);
        $this->assertFalse($result);
    }

    public function testAddPassengerNotEnoughCreditReturnsFalse(): void
    {
        $ride = $this->createRideWithSeats(3);
        $user = $this->createMock(User::class);
        $user->method('getCredit')->willReturn(10.0); // less than ride price
        $ride->setPricePerson(50);

        $result = $this->rideService->addPassenger($ride, $user);
        $this->assertFalse($result);
    }

    public function testAddPassengerNoSeatsAvailableReturnsFalse(): void
    {
        $ride = $this->createRideWithSeats(0);
        $user = $this->createMock(User::class);
        $user->method('getCredit')->willReturn(100.0);
        $ride->setPricePerson(50);

        $result = $this->rideService->addPassenger($ride, $user);
        $this->assertFalse($result);
    }

    // -------------------- REMOVE PASSENGER --------------------

    public function testRemovePassenger(): void
    {
        $ride = $this->createRideWithSeats(3);
        $user = $this->createMock(User::class);
        $user->method('getCredit')->willReturn(100.0);

        $ride->addPassenger($user);
        $this->assertTrue($ride->getPassengers()->contains($user));

        $this->rideService->removePassenger($ride, $user);
        $this->assertFalse($ride->getPassengers()->contains($user));
    }

    // -------------------- HELPERS --------------------

    private function createRideWithSeats(int $seats): Ride
    {
        $ride = new Ride();
        $ride->setNbPlacesOffered($seats);
        return $ride;
    }
}
