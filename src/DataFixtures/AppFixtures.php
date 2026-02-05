<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\VehicleBrand;
use App\Entity\User;
use App\Entity\UserAddress;
use App\Entity\Vehicle;
use App\Entity\RideStatus;
use App\Entity\EvaluationStatus;
use App\Entity\Ride;
use App\Entity\Evaluation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // -------------------- ROLES --------------------
        $rolesData = [
            1 => 'ROLE_ADM',
            2 => 'ROLE_DRIVER',
            3 => 'ROLE_PASSENGER',
            4 => 'ROLE_EMPLOYEE',
        ];

        foreach ($rolesData as $id => $name) {
            $role = new Role();
            $role->setName($name);
            $manager->persist($role);
            $this->addReference('role_' . $id, $role);
        }

        // -------------------- VEHICLE BRANDS --------------------
        $brandsData = [
            1 => 'Porsche',
            2 => 'Peugeot',
            3 => 'Hyundai',
            4 => 'Nissan',
            5 => 'Audi',
            6 => 'BMW',
            7 => 'Volkswagen',
            8 => 'Tesla'
        ];

        foreach ($brandsData as $id => $brandName) {
            $brand = new VehicleBrand();
            $brand->setBrand($brandName);
            $manager->persist($brand);
            $this->addReference('brand_' . $id, $brand);
        }

        // -------------------- USERS --------------------
        $usersData = [
            ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe', 'nickname' => 'JDoe', 'email' => 'john.doe@test.com', 'password' => 'Asdfg123!', 'telephone' => '012345678', 'birthday' => '1981-12-12', 'credit' => 35, 'avg_rating' => 2.2, 'roles' => [1, 2]],
            ['id' => 3, 'first_name' => 'Mary', 'last_name' => 'Doe', 'nickname' => 'MDoe', 'email' => 'mary.doe@test.com', 'password' => 'Asdfg123!', 'telephone' => '02345678', 'birthday' => '2000-12-12', 'credit' => 20, 'avg_rating' => 0, 'roles' => [3]],
            ['id' => 5, 'first_name' => 'Jack', 'last_name' => 'Doe', 'nickname' => 'JaDoe55', 'email' => 'jack.joe@test.com', 'password' => 'Asdfg123!', 'telephone' => '03456789', 'birthday' => '1999-12-12', 'credit' => 10, 'avg_rating' => 4.1, 'roles' => [2]],
            ['id' => 6, 'first_name' => 'Jean', 'last_name' => 'Dupont', 'nickname' => 'JeDu@95', 'email' => 'user@example.com', 'password' => '$2y$13$hash', 'telephone' => '0612345678', 'birthday' => '1995-06-15', 'credit' => 20, 'avg_rating' => 0, 'roles' => [3]],
            ['id' => 7, 'first_name' => 'Marie', 'last_name' => 'Dupont-Laurie', 'nickname' => 'M.Dupont', 'email' => 'test@test.com', 'password' => '$2y$13$hash', 'telephone' => '00000000', 'birthday' => '1995-01-12', 'credit' => 20, 'avg_rating' => 0, 'roles' => [3]],
            ['id' => 8, 'first_name' => 'Marie', 'last_name' => 'Dupont-Laurie', 'nickname' => 'M.Dupont2', 'email' => 'teste@test.com', 'password' => '$2y$13$hash', 'telephone' => '00000000', 'birthday' => '1976-01-21', 'credit' => 20, 'avg_rating' => 0, 'roles' => [3]],
            ['id' => 9, 'first_name' => 'Marie', 'last_name' => 'Dupont-Laurie', 'nickname' => 'M.Dupont3', 'email' => 'test2@test.com', 'password' => '$2y$13$hash', 'telephone' => '00000000', 'birthday' => '1986-01-24', 'credit' => 20, 'avg_rating' => 0, 'roles' => [3]],
            // DEMO USERS
            ['id' => 100, 'first_name' => 'Admin', 'last_name' => 'EcoRide', 'nickname' => 'AdminUser', 'email' => 'admin@ecoride.test', 'password' => '$2y$13$CSp6sJQTiK.eIWoID88t4.cIXiq/JL14kq/SlV1MltwZk9P6vK6yW', 'telephone' => '0600000001', 'birthday' => '1980-01-01', 'credit' => 100, 'avg_rating' => 5, 'roles' => [1]],
            ['id' => 101, 'first_name' => 'Emma', 'last_name' => 'Employee', 'nickname' => 'EmployeeUser', 'email' => 'employee@ecoride.test', 'password' => '$2y$13$CSp6sJQTiK.eIWoID88t4.cIXiq/JL14kq/SlV1MltwZk9P6vK6yW', 'telephone' => '0600000002', 'birthday' => '1990-01-01', 'credit' => 50, 'avg_rating' => 4.5, 'roles' => [4]],
            ['id' => 102, 'first_name' => 'David', 'last_name' => 'Driver', 'nickname' => 'DriverUser', 'email' => 'driver@ecoride.test', 'password' => '$2y$13$CSp6sJQTiK.eIWoID88t4.cIXiq/JL14kq/SlV1MltwZk9P6vK6yW', 'telephone' => '0600000003', 'birthday' => '1992-01-01', 'credit' => 30, 'avg_rating' => 4.8, 'roles' => [2]],
            ['id' => 103, 'first_name' => 'Paul', 'last_name' => 'Passenger', 'nickname' => 'PassengerUser', 'email' => 'passenger@ecoride.test', 'password' => '$2y$13$CSp6sJQTiK.eIWoID88t4.cIXiq/JL14kq/SlV1MltwZk9P6vK6yW', 'telephone' => '0600000004', 'birthday' => '1998-01-01', 'credit' => 20, 'avg_rating' => 4, 'roles' => [3]],
            ['id' => 104, 'first_name' => 'Claire', 'last_name' => 'Hybrid', 'nickname' => 'HybridUser', 'email' => 'hybrid@ecoride.test', 'password' => '$2y$13$CSp6sJQTiK.eIWoID88t4.cIXiq/JL14kq/SlV1MltwZk9P6vK6yW', 'telephone' => '0600000005', 'birthday' => '1995-01-01', 'credit' => 40, 'avg_rating' => 4.6, 'roles' => [2, 3]],
        ];

        foreach ($usersData as $u) {
            /** @var User $user */
            $user = new User();
            $user->setFirstName($u['first_name']);
            $user->setLastName($u['last_name']);
            $user->setNickname($u['nickname']);
            $user->setEmail($u['email']);
            $user->setBirthday(new \DateTimeImmutable($u['birthday']));
            $user->setCredit($u['credit']);
            $user->setAvgRating($u['avg_rating']);
            $user->setTelephone($u['telephone']);

            // Hash password
            if (str_starts_with($u['password'], '$2y$13$')) {
                $user->setPassword($u['password']);
            } else {
                $user->setPassword($this->passwordHasher->hashPassword($user, $u['password']));
            }

            $manager->persist($user);
            $this->addReference('user_' . $u['id'], $user);

            // Roles (ManyToMany)
            foreach ($u['roles'] as $roleId) {
                /** @var Role $role */
                $role = $this->getReference('role_' . $roleId, Role::class);
                $user->addRole($role);
            }
        }

        // -------------------- USER ADDRESS --------------------
        $addressesData = [
            ['user_id' => 1, 'number' => '10', 'street' => 'Rue de Paris', 'city' => 'Paris', 'zipcode' => '75000', 'country' => 'France'],
            ['user_id' => 3, 'number' => '25', 'street' => 'Rue Victor Hugo', 'city' => 'Lyon', 'zipcode' => '69000', 'country' => 'France'],
            ['user_id' => 5, 'number' => '18', 'street' => 'Avenue Jean Jaurès', 'city' => 'Marseille', 'zipcode' => '13000', 'country' => 'France'],
            ['user_id' => 6, 'number' => '3', 'street' => 'Rue Nationale', 'city' => 'Lille', 'zipcode' => '59000', 'country' => 'France'],
            ['user_id' => 7, 'number' => '40', 'street' => 'Boulevard Gambetta', 'city' => 'Nice', 'zipcode' => '06000', 'country' => 'France'],
        ];

        foreach ($addressesData as $addr) {
            /** @var User $user */
            $user = $this->getReference('user_' . $addr['user_id'], User::class);
            $address = new UserAddress();
            $address->setUser($user);
            $address->setNumber($addr['number']);
            $address->setStreet($addr['street']);
            $address->setCity($addr['city']);
            $address->setZipcode($addr['zipcode']);
            $address->setCountry($addr['country']);
            $manager->persist($address);
        }

        // -------------------- VEHICLES --------------------
        $vehiclesData = [
            ['id' => 1, 'brand' => 5, 'model' => 'Q4', 'color' => 'Noir', 'registration' => 'AA-123-AA', 'first_rg_date' => '2020-12-12 16:11:14', 'electric' => false],
            ['id' => 2, 'brand' => 8, 'model' => 'Model 3', 'color' => 'Blanc', 'registration' => 'BB-456-BB', 'first_rg_date' => '2017-12-12 16:15:23', 'electric' => true],
            ['id' => 3, 'brand' => 6, 'model' => 'X1', 'color' => 'Bleu foncé', 'registration' => 'CC-789-CC', 'first_rg_date' => '2025-12-12 16:18:26', 'electric' => false],
            ['id' => 100, 'brand' => 7, 'model' => 'Golf', 'color' => 'Gris', 'registration' => 'DD-111-DD', 'first_rg_date' => '2021-05-05 10:00:00', 'electric' => false],
        ];

        foreach ($vehiclesData as $v) {
            /** @var VehicleBrand $brand */
            $brand = $this->getReference('brand_' . $v['brand'], VehicleBrand::class);
            $vehicle = new Vehicle();
            $vehicle->setVehicleBrand($brand);
            $vehicle->setModel($v['model']);
            $vehicle->setColor($v['color']);
            $vehicle->setRegistration($v['registration']);
            $vehicle->setFirstRgDate(new \DateTime($v['first_rg_date']));
            $vehicle->setElectric($v['electric']);
            $manager->persist($vehicle);
            $this->addReference('vehicle_' . $v['id'], $vehicle);
        }

        // -------------------- USER VEHICLES (ManyToMany) --------------------
        $userVehiclesData = [
            ['user' => 5, 'vehicle' => 1],
            ['user' => 5, 'vehicle' => 2],
            ['user' => 1, 'vehicle' => 3],
            ['user' => 102, 'vehicle' => 100],
        ];

        foreach ($userVehiclesData as $uv) {
            /** @var User $user */
            $user = $this->getReference('user_' . $uv['user'], User::class);
            /** @var Vehicle $vehicle */
            $vehicle = $this->getReference('vehicle_' . $uv['vehicle'], Vehicle::class);
            $user->addVehicle($vehicle);
        }

        // -------------------- RIDE STATUS --------------------
        $rideStatusData = [
            1 => 'PENDING',
            2 => 'CONFIRMED',
            3 => 'AWAITING_PICKUP',
            4 => 'IN_PROGRESS',
            5 => 'COMPLETED',
            6 => 'CANCELLED',
            7 => 'NO_SHOW',
            8 => 'DRIVER_NO_SHOW'
        ];
        foreach ($rideStatusData as $id => $label) {
            $status = new RideStatus();
            $status->setLabel($label);
            $manager->persist($status);
            $this->addReference('ride_status_' . $id, $status);
        }

        // -------------------- EVALUATION STATUS --------------------
        $evalStatusData = [
            1 => 'EVAL_CREATED',
            2 => 'EVAL_AWAITING_PASS',
            3 => 'EVAL_SUBMITTED',
            4 => 'EVAL_UNDER_REVIEW',
            5 => 'PAYMENT_APPROVED',
            6 => 'PAYMENT_DENIED',
            7 => 'PAYMENT_BLOCKED'
        ];
        foreach ($evalStatusData as $id => $label) {
            $status = new EvaluationStatus();
            $status->setLabel($label);
            $manager->persist($status);
            $this->addReference('eval_status_' . $id, $status);
        }

        // -------------------- RIDES --------------------
        $ridesData = [
            ['id' => 3, 'driver' => 5, 'status' => 1, 'vehicle' => 1, 'origin' => 'Paris', 'pick' => '7, Pl. Adolphe Chérioux', 'dep_date' => '2026-02-20 16:58:44', 'dep_time' => '14:00:00', 'dest' => 'Lyon', 'drop' => 'Gare Part Dieu', 'arr_date' => '2026-02-20 16:58:44', 'arr_time' => '16:30:00', 'nb_places' => 3, 'nb_available' => 3, 'price' => 27, 'smokers' => 0, 'animals' => 0, 'other' => 'Merci de ne pas manger dans la voiture', 'duration' => 150],
            ['id' => 4, 'driver' => 5, 'status' => 3, 'vehicle' => 2, 'origin' => 'Paris', 'pick' => '7, Pl. Adolphe Chérioux', 'dep_date' => '2026-02-20 16:58:44', 'dep_time' => '18:00:00', 'dest' => 'Lyon', 'drop' => 'Gare Part Dieu', 'arr_date' => '2026-02-20 16:58:44', 'arr_time' => '21:00:00', 'nb_places' => 2, 'nb_available' => 1, 'price' => 25.5, 'smokers' => 1, 'animals' => 0, 'other' => null, 'duration' => 180],
            ['id' => 5, 'driver' => 1, 'status' => 2, 'vehicle' => 1, 'origin' => 'Paris', 'pick' => '39, rue Gabriel Lamé', 'dep_date' => '2026-02-20 17:14:21', 'dep_time' => '10:00:00', 'dest' => 'Lyon', 'drop' => 'Faculté de Médicine Lyon Est', 'arr_date' => '2026-02-20 17:14:21', 'arr_time' => '13:30:00', 'nb_places' => 2, 'nb_available' => 2, 'price' => 26.5, 'smokers' => 1, 'animals' => 1, 'other' => null, 'duration' => 210],
            ['id' => 100, 'driver' => 102, 'status' => 3, 'vehicle' => 100, 'origin' => 'Nice', 'pick' => 'Gare de Nice', 'dep_date' => '2026-03-01 09:00:00', 'dep_time' => '09:00:00', 'dest' => 'Marseille', 'drop' => 'Gare St Charles', 'arr_date' => '2026-03-01 12:00:00', 'arr_time' => '12:00:00', 'nb_places' => 3, 'nb_available' => 3, 'price' => 30, 'smokers' => 0, 'animals' => 0, 'other' => 'Trajet demo', 'duration' => 120],
        ];

        foreach ($ridesData as $r) {
            /** @var User $user */
            $user = $this->getReference('user_' . $r['driver'], User::class);
            /** @var RideStatus $rideStatus */
            $rideStatus = $this->getReference('ride_status_' . $r['status'], RideStatus::class);
            /** @var Vehicle $vehicle */
            $vehicle = $this->getReference('vehicle_' . $r['vehicle'], Vehicle::class);
            $ride = new Ride();
            $ride->setDriver($user);
            $ride->setRideStatus($rideStatus);
            $ride->setVehicle($vehicle);
            $ride->setOriginCity($r['origin']);
            $ride->setPickPoint($r['pick']);
            $ride->setDepartureDate(new \DateTime($r['dep_date']));
            $ride->setDepartureIntendedTime(\DateTime::createFromFormat('H:i:s', $r['dep_time']));
            $ride->setDestinyCity($r['dest']);
            $ride->setDropPoint($r['drop']);
            $ride->setArrivalDate(new \DateTime($r['arr_date']));
            $ride->setArrivalEstimatedTime(\DateTime::createFromFormat('H:i:s', $r['arr_time']));
            $ride->setNbPlacesOffered($r['nb_places']);
            $ride->setNbPlacesAvailable($r['nb_available']);
            $ride->setPricePerson($r['price']);
            $ride->setSmokersAllowed($r['smokers']);
            $ride->setAnimalsAllowed($r['animals']);
            $ride->setOtherPreferences($r['other']);
            $ride->setEstimatedDuration($r['duration']);
            $manager->persist($ride);
            $this->addReference('ride_' . $r['id'], $ride);
        }

        // -------------------- RIDE PASSENGERS (ManyToMany) --------------------
        $ridePassengersData = [
            ['ride' => 3, 'user' => 3],
            ['ride' => 4, 'user' => 6],
            ['ride' => 5, 'user' => 7],
            ['ride' => 100, 'user' => 103],
        ];

        foreach ($ridePassengersData as $rp) {
            /** @var Ride $ride */
            $ride = $this->getReference('ride_' . $rp['ride'], Ride::class);

            /** @var User $user */
            $user = $this->getReference('user_' . $rp['user'], User::class);

            $ride->addPassenger($user);
        }

        // -------------------- EVALUATIONS --------------------
        $evalData = [
            ['id' => 1, 'ride' => 3, 'passenger' => 3, 'status' => 3, 'treated_by' => null, 'validation_passenger' => 1, 'rate' => 5, 'comment' => 'Trajet très agréable'],
            ['id' => 2, 'ride' => 100, 'passenger' => 103, 'status' => 3, 'treated_by' => 102, 'validation_passenger' => 1, 'rate' => 5, 'comment' => 'Super trajet démo'],
        ];

        foreach ($evalData as $e) {
            /** @var Ride $ride */
            $ride = $this->getReference('ride_' . $e['ride'], Ride::class);

            /** @var User $passenger */
            $passenger = $this->getReference('user_' . $e['passenger'], User::class);

            /** @var EvaluationStatus $status */
            $status = $this->getReference('eval_status_' . $e['status'], EvaluationStatus::class);

            $evaluation = new Evaluation();
            $evaluation->setRide($ride);
            $evaluation->setPassenger($passenger);
            $evaluation->setStatus($status);

            if ($e['treated_by']) {
                /** @var User $treatedBy */
                $treatedBy = $this->getReference('user_' . $e['treated_by'], User::class);
                $evaluation->setTreatedBy($treatedBy);
            }

            $evaluation->setValidationPassenger($e['validation_passenger']);
            $evaluation->setRate($e['rate']);
            $evaluation->setComment($e['comment']);
            $evaluation->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($evaluation);
        }

        $manager->flush();
    }
}
