<?php

namespace App\Factory;

use App\Entity\User;
use App\Entity\UserAddress;
use App\Service\Security\SecurityService;

class UserAddressFactory
{
    public function __construct(
        private readonly SecurityService $security
    ) {}

    public function create(
        array $data,
        User $user
    ): UserAddress {
        $address = new UserAddress();

        $address
            ->setStreet(
                $this->security->sanitizeString($data['street'] ?? '')
            )
            ->setNumber(
                $this->security->sanitizeString($data['number'] ?? '', 6)
            )
            ->setComplement(
                $this->security->sanitizeString($data['complement'] ?? '')
            )
            ->setCity(
                $this->security->sanitizeString($data['city'] ?? '', 60)
            )
            ->setZipcode(
                $this->security->sanitizeString($data['zipcode'] ?? '', 10)
            )
            ->setCountry(
                $this->security->sanitizeString($data['country'] ?? '', 60)
            )
            ->setUser($user);

        return $address;
    }
}
