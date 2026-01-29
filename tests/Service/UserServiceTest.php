<?php

namespace App\Tests\Service;

use App\DTO\UserSignupRequest;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\UserAddress;
use App\Service\SecurityService;
use App\Service\UserService;
use App\Repository\UserRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserServiceTest extends TestCase
{
    private UserRepository $userRepo;
    private RoleRepository $roleRepo;
    /** @var \PHPUnit\Framework\MockObject\MockObject&EntityManagerInterface */
    private EntityManagerInterface $em;
    /** @var \PHPUnit\Framework\MockObject\MockObject&SecurityService */
    private SecurityService $security;
    /** @var \PHPUnit\Framework\MockObject\MockObject&UserPasswordHasherInterface */
    private UserPasswordHasherInterface $passwordHasher;
    private UserService $userService;

    protected function setUp(): void
    {
        $this->userRepo = $this->createMock(UserRepository::class);
        $this->roleRepo = $this->createMock(RoleRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->security = $this->createMock(SecurityService::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $this->userService = new UserService(
            $this->userRepo,
            $this->roleRepo,
            $this->em,
            $this->security,
            $this->passwordHasher
        );
    }

    // -------------------- BASIC VALIDATION --------------------

    public function testCreateUserFailsWithEmptyFirstName(): void
    {
        $dto = $this->createValidDTO(['firstName' => '']);
        $response = $this->userService->createUser($dto);
        $this->assertSame('INVALID_FIRST_NAME', $response->status);
    }

    public function testCreateUserFailsWithInvalidEmail(): void
    {
        $dto = $this->createValidDTO(['email' => 'invalid-email']);
        $response = $this->userService->createUser($dto);
        $this->assertSame('INVALID_EMAIL', $response->status);
    }

    public function testCreateUserFailsUnderage(): void
    {
        $birthday = (new \DateTimeImmutable())->modify('-17 years');
        $dto = $this->createValidDTO(['birthday' => $birthday]);
        $response = $this->userService->createUser($dto);
        $this->assertSame('UNDERAGE', $response->status);
    }

    // -------------------- ROLE ASSIGNMENT --------------------

    public function testCreateUserAssignsRolesCorrectly(): void
    {
        $dto = $this->createValidDTO(['usageType' => 'BOTH']);

        $rolePassenger = new Role();
        $reflection = new \ReflectionProperty(Role::class, 'id');
        $reflection->setValue($rolePassenger, 1);

        $roleDriver = new Role();
        $reflection = new \ReflectionProperty(Role::class, 'id');
        $reflection->setValue($roleDriver, 2);

        $this->roleRepo->method('findRoleByName')
            ->willReturnCallback(fn($name) => match ($name) {
                'ROLE_PASSENGER' => $rolePassenger,
                'ROLE_DRIVER' => $roleDriver,
                default => null,
            });

        $this->security->method('cleanNickname')->willReturn($dto->nickname);
        $this->userRepo->method('existsByEmail')->willReturn(false);
        $this->userRepo->method('existsByNickname')->willReturn(false);
        $this->passwordHasher->method('hashPassword')->willReturn('hashed-pass');

        $this->em->method('persist')->willReturnCallback(function ($entity) {
            if ($entity instanceof User) {
                $reflection = new \ReflectionProperty(User::class, 'id');
                $reflection->setValue($entity, 123);
            }
        });

        $response = $this->userService->createUser($dto);

        $this->assertSame('SUCCESS', $response->status);
        $this->assertNotNull($response->id);
    }

    // -------------------- UNIQUENESS CHECKS --------------------

    public function testCreateUserFailsIfEmailExists(): void
    {
        $dto = $this->createValidDTO();
        $this->userRepo->method('existsByEmail')->willReturn(true);

        $response = $this->userService->createUser($dto);
        $this->assertSame('EMAIL_ALREADY_EXISTS', $response->status);
    }

    public function testCreateUserFailsIfNicknameExists(): void
    {
        $dto = $this->createValidDTO();
        $this->userRepo->method('existsByEmail')->willReturn(false);
        $this->userRepo->method('existsByNickname')->willReturn(true);

        $response = $this->userService->createUser($dto);
        $this->assertSame('NICKNAME_ALREADY_EXISTS', $response->status);
    }

    // -------------------- PROFILE PICTURE --------------------

    public function testCreateUserFailsWithInvalidProfilePicture(): void
    {
        $dto = $this->createValidDTO();
        $file = $this->createMock(UploadedFile::class);
        $file->method('getSize')->willReturn(3 * 1024 * 1024); // 3MB
        $file->method('getMimeType')->willReturn('image/gif');

        $dto->profilePicture = $file;

        $this->userRepo->method('existsByEmail')->willReturn(false);
        $this->userRepo->method('existsByNickname')->willReturn(false);

        $response = $this->userService->createUser($dto);
        $this->assertSame('INVALID_PROFILE_PICTURE', $response->status);
    }

    // -------------------- ADDRESS --------------------

    public function testCreateUserWithAddress(): void
    {
        $dto = $this->createValidDTO(['address' => [
            'street' => 'Rue A',
            'number' => '12',
            'complement' => '',
            'city' => 'Paris',
            'zipcode' => '75001',
            'country' => 'France'
        ]]);

        $this->security->method('cleanNickname')->willReturn($dto->nickname);
        $this->security->method('sanitizeString')->willReturnCallback(fn($s) => $s);
        $this->userRepo->method('existsByEmail')->willReturn(false);
        $this->userRepo->method('existsByNickname')->willReturn(false);
        $this->passwordHasher->method('hashPassword')->willReturn('hashed-pass');

        $response = $this->userService->createUser($dto);

        $this->assertSame('SUCCESS', $response->status);
    }

    // -------------------- HELPERS --------------------

    private function createValidDTO(array $overrides = []): UserSignupRequest
    {
        $dto = new UserSignupRequest();
        $dto->firstName = $overrides['firstName'] ?? 'John';
        $dto->lastName = $overrides['lastName'] ?? 'Doe';
        $dto->nickname = $overrides['nickname'] ?? 'johnny';
        $dto->email = $overrides['email'] ?? 'john@example.com';
        $dto->telephone = $overrides['telephone'] ?? '+33612345678';
        $dto->password = $overrides['password'] ?? 'securepass';
        $dto->birthday = $overrides['birthday'] ?? new \DateTimeImmutable('2000-01-01');
        $dto->usageType = $overrides['usageType'] ?? 'PASSENGER';
        $dto->profilePicture = $overrides['profilePicture'] ?? null;
        $dto->address = $overrides['address'] ?? null;

        return $dto;
    }
}
