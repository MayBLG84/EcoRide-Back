<?php

namespace App\Factory;

use App\DTO\UserSignupRequest;
use App\Entity\Role;
use App\Entity\User;
use App\Service\Security\SecurityService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFactory
{
    public function __construct(
        private readonly SecurityService $security,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    /**
     * @param Role[] $roles
     */
    public function createFromSignup(
        UserSignupRequest $dto,
        array $roles
    ): User {
        $user = new User();

        $user
            ->setFirstName(
                $this->security->sanitizeString($dto->firstName, 50)
            )
            ->setLastName(
                $this->security->sanitizeString($dto->lastName)
            )
            ->setNickname(
                $this->security->cleanNickname($dto->nickname)
            )
            ->setEmail(
                $this->security->normalizeEmail($dto->email)
            )
            ->setTelephone(
                $this->security->sanitizeString($dto->telephone, 15)
            )
            ->setBirthday($dto->birthday)
            ->setCredit(20.00)
            ->setAvgRating(0.0);

        $hashedPassword = $this->passwordHasher
            ->hashPassword($user, $dto->password);

        $user->setPassword($hashedPassword);

        foreach ($roles as $role) {
            $user->addRole($role);
        }

        return $user;
    }
}
