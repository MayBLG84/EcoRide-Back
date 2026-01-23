<?php

namespace App\Service;

use App\DTO\UserSignupRequest;
use App\DTO\UserSignupResponse;
use App\Entity\User;
use App\Entity\UserAddress;
use App\Entity\Role;
use App\Repository\UserRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\SecurityServiceService;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepo,
        private readonly RoleRepository $roleRepo,
        private readonly EntityManagerInterface $em,
        private readonly SecurityService $security,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    public function createUser(UserSignupRequest $dto): UserSignupResponse
    {
        $response = new UserSignupResponse();

        // ─────────────────────────────────────
        // Basic validation
        // ─────────────────────────────────────
        if (empty($dto->firstName)) {
            return $this->fail($response, 'INVALID_FIRST_NAME');
        }

        if (empty($dto->lastName)) {
            return $this->fail($response, 'INVALID_LAST_NAME');
        }

        if (empty($dto->nickname)) {
            return $this->fail($response, 'INVALID_NICKNAME');
        }

        if (!filter_var($dto->email, FILTER_VALIDATE_EMAIL)) {
            return $this->fail($response, 'INVALID_EMAIL');
        }

        if (!preg_match('/^\+?[0-9]{8,15}$/', $dto->telephone)) {
            return $this->fail($response, 'INVALID_TELEPHONE');
        }

        if (strlen($dto->password) < 8) {
            return $this->fail($response, 'INVALID_PASSWORD');
        }

        if (!$dto->birthday instanceof \DateTimeImmutable) {
            return $this->fail($response, 'INVALID_BIRTHDAY');
        }

        // ─────────────────────────────────────
        // Age validation (18+)
        // ─────────────────────────────────────
        if (!$this->isAtLeastAge($dto->birthday, 18)) {
            return $this->fail($response, 'UNDERAGE');
        }

        // ─────────────────────────────────────
        // UsageType → Roles
        // ─────────────────────────────────────
        $roles = match ($dto->usageType) {
            'PASSENGER' => ['ROLE_PASSENGER'],
            'DRIVER'    => ['ROLE_DRIVER'],
            'BOTH'      => ['ROLE_PASSENGER', 'ROLE_DRIVER'],
            default     => null,
        };

        if ($roles === null) {
            return $this->fail($response, 'INVALID_USAGE_TYPE');
        }

        // ─────────────────────────────────────
        // Normalize values
        // ─────────────────────────────────────
        $email = mb_strtolower(trim($dto->email));
        $nickname = $this->security->cleanNickname($dto->nickname);

        // ─────────────────────────────────────
        // Uniqueness checks
        // ─────────────────────────────────────
        if ($this->userRepo->existsByEmail($email)) {
            $response->status = 'EMAIL_ALREADY_EXISTS';
            return $response;
        }

        if ($this->userRepo->existsByNickname($nickname)) {
            $response->status = 'NICKNAME_ALREADY_EXISTS';
            return $response;
        }

        // ─────────────────────────────────────
        // Profile picture validation
        // ─────────────────────────────────────
        if ($dto->profilePicture instanceof UploadedFile) {
            $allowedMime = ['image/jpeg', 'image/png', 'image/jpg'];
            $sizeMb = $dto->profilePicture->getSize() / 1024 / 1024;

            if (!in_array($dto->profilePicture->getMimeType(), $allowedMime, true) || $sizeMb > 2) {
                $response->status = 'INVALID_PROFILE_PICTURE';
                return $response;
            }
        }


        // ─────────────────────────────────────
        // Create User entity
        // ─────────────────────────────────────
        $user = new User();
        $user
            ->setFirstName($this->security->sanitizeString($dto->firstName))
            ->setLastName($this->security->sanitizeString($dto->lastName))
            ->setNickname($nickname)
            ->setEmail($email)
            ->setTelephone($dto->telephone)
            ->setBirthday($dto->birthday)
            ->setPassword($this->passwordHasher->hashPassword($user, $dto->password))
            ->setCredit(20.00)
            ->setAvgRating(0.0);

        foreach ($roles as $roleName) {
            $roleEntity = $this->roleRepo->findRoleByName($roleName);
            if ($roleEntity) {
                $user->addRole($roleEntity);
            }
        }

        if ($dto->profilePicture instanceof UploadedFile) {
            $user->setPhoto(file_get_contents($dto->profilePicture->getRealPath()));
        }

        // ─────────────────────────────────────
        // Address (optional)
        // ─────────────────────────────────────
        if (!empty($dto->address)) {
            $address = new UserAddress();
            $address
                ->setStreet($this->security->sanitizeString($dto->address['street'] ?? ''))
                ->setNumber($this->security->sanitizeString($dto->address['number'] ?? ''))
                ->setComplement($this->security->sanitizeString($dto->address['complement'] ?? ''))
                ->setCity($this->security->sanitizeString($dto->address['city'] ?? ''))
                ->setZipcode($this->security->sanitizeString($dto->address['zipcode'] ?? ''))
                ->setCountry($this->security->sanitizeString($dto->address['country'] ?? ''))
                ->setUser($user)
                ->setCreatedAt(new \DateTimeImmutable());

            $user->setAddress($address);
            $this->em->persist($address);
        }

        // ─────────────────────────────────────
        // Persist
        // ─────────────────────────────────────
        $this->em->persist($user);
        $this->em->flush();

        // ─────────────────────────────────────
        // Response
        // ─────────────────────────────────────
        $response->status = 'SUCCESS';
        $response->id = $user->getId();
        $response->firstName = $user->getFirstName();
        $response->lastName = $user->getLastName();
        $response->nickname = $user->getNickname();
        $response->email = $user->getEmail();
        $response->createdAt = $user->getCreatedAt()->format(DATE_ATOM);

        return $response;
    }

    private function fail(UserSignupResponse $response, string $status): UserSignupResponse
    {
        $response->status = $status;
        return $response;
    }

    public function isNicknameExists(string $nickname): bool
    {
        $nickname = $this->security->cleanNickname($nickname);
        return $this->userRepo->existsByNickname($nickname);
    }

    public function isAtLeastAge(\DateTimeImmutable $birthday, int $minAge): bool
    {
        $today = new \DateTimeImmutable('today');
        $age = $today->diff($birthday)->y;

        return $age >= $minAge;
    }
}
