<?php

namespace App\Service;

use App\Entity\Ride;
use App\Entity\User;
use App\Entity\Vehicle;
use Doctrine\ORM\EntityManagerInterface;

class RideService
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Create a new Ride and assign the driver.
     *
     * @param User $driver
     * @param array $data [originCity, destinyCity, vehicle, nbPlacesOffered, pricePerson]
     * @return Ride
     *
     * @throws \InvalidArgumentException if the vehicle does not belong to the driver
     */
    public function createRide(User $driver, array $data): Ride
    {
        /** @var Vehicle $vehicle */
        $vehicle = $data['vehicle'];

        // Validate that the vehicle belongs to the driver
        if (!$driver->getVehicles()->contains($vehicle)) {
            throw new \InvalidArgumentException("Le véhicule doit appartenir au conducteur du trajet.");
        }

        $ride = new Ride();
        $ride->setDriver($driver)
            ->setOriginCity($data['originCity'])
            ->setDestinyCity($data['destinyCity'])
            ->setVehicle($vehicle)
            ->setNbPlacesOffered($data['nbPlacesOffered'])
            ->setPricePerson($data['pricePerson']);

        $this->em->persist($ride);
        $this->em->flush();

        return $ride;
    }

    /**
     * Add a passenger to a ride.
     *
     * @param Ride $ride
     * @param User $user
     * @return bool true if added successfully, false otherwise
     */
    public function addPassenger(Ride $ride, User $user): bool
    {
        // Check if the user is already a passenger
        if ($ride->getPassengers()->contains($user)) {
            return false;
        }

        // Check if user has enough credit
        if ($user->getCredit() < $ride->getPricePerson()) {
            return false; // Front should redirect to addCredits
        }

        // Check if there are available seats
        if ($ride->getNbPlacesAvailable() <= 0) {
            return false; // Ride is full
        }

        // Add the passenger
        $ride->addPassenger($user);

        // nbPlacesAvailable is updated automatically in Ride::updateNbPlacesAvailable()
        $this->em->persist($ride);
        $this->em->flush();

        return true;
    }

    /**
     * Remove a passenger from a ride
     *
     * @param Ride $ride
     * @param User $user
     */
    public function removePassenger(Ride $ride, User $user): void
    {
        $ride->removePassenger($user);
        $this->em->persist($ride);
        $this->em->flush();
    }
}
