<?php

namespace App\Tests\DTO;

use App\DTO\UserSignupRequest;
use PHPUnit\Framework\TestCase;

class UserSignupRequestTest extends TestCase
{
    public function testCanPopulateUserSignupRequest(): void
    {
        $dto = new UserSignupRequest();

        $dto->firstName = 'Jean';
        $dto->lastName = 'Dupont';
        $dto->nickname = 'jeand';
        $dto->email = 'user@example.com';
        $dto->password = 'StrongPassword123!';
        $dto->birthday = new \DateTimeImmutable('1995-06-15');
        $dto->usageType = 'BOTH';
        $dto->telephone = '+33612345678';
        $dto->address = [
            'number' => '12',
            'street' => 'Rue X',
            'city' => 'Paris',
            'zipcode' => '75000',
            'country' => 'France'
        ];

        $this->assertSame('Jean', $dto->firstName);
        $this->assertSame('Dupont', $dto->lastName);
        $this->assertSame('jeand', $dto->nickname);
        $this->assertSame('user@example.com', $dto->email);
        $this->assertInstanceOf(\DateTimeImmutable::class, $dto->birthday);
        $this->assertSame('BOTH', $dto->usageType);
        $this->assertIsArray($dto->address);
        $this->assertArrayHasKey('street', $dto->address);
        $this->assertArrayHasKey('number', $dto->address);
        $this->assertArrayHasKey('city', $dto->address);
    }
}
