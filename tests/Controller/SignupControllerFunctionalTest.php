<?php

namespace App\Tests\Controller;

use App\DTO\UserSignupResponse;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SignupControllerFunctionalTest extends WebTestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject&UserService */
    private $userService;

    private $client;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();

        $this->userService = $this->createMock(UserService::class);
        $this->client->getContainer()->set(UserService::class, $this->userService);
    }

    public function testCreateUserSuccess(): void
    {
        $this->userService->method('createUser')
            ->willReturn(new UserSignupResponse(
                'SUCCESS',
                1,
                'John',
                'Doe',
                'johnny',
                'john@example.com',
                20.0,
                new \DateTimeImmutable()
            ));

        $file = new UploadedFile(tempnam(sys_get_temp_dir(), 'profile'), 'profile.jpg', 'image/jpeg', null, true);

        $this->client->request('POST', '/signup', [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'nickname' => 'johnny',
            'email' => 'john@example.com',
            'telephone' => '+33612345678',
            'password' => 'securepass',
            'birthday' => '2000-01-01',
            'usageType' => 'PASSENGER',
        ], ['profilePicture' => $file]);

        $response = $this->client->getResponse();

        $this->assertSame(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('SUCCESS', $data['status']);
        $this->assertSame(1, $data['id']);
    }

    public function testCreateUserConflict(): void
    {
        $this->userService->method('createUser')
            ->willReturn(new UserSignupResponse(
                'EMAIL_ALREADY_EXISTS',
                null,
                null,
                null,
                null,
                null,
                null,
                null
            ));

        $this->client->request('POST', '/signup', [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'nickname' => 'johnny',
            'email' => 'john@example.com',
            'telephone' => '+33612345678',
            'password' => 'securepass',
            'birthday' => '2000-01-01',
            'usageType' => 'PASSENGER',
        ]);

        $response = $this->client->getResponse();

        $this->assertSame(409, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('EMAIL_ALREADY_EXISTS', $data['status']);
    }

    public function testNicknameExistsReturnsTrue(): void
    {
        $this->userService->method('isNicknameExists')->willReturn(true);

        $this->client->request('GET', '/signup/nickname-exists', ['nick' => 'johnny']);
        $response = $this->client->getResponse();

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['exists']);
    }

    public function testNicknameExistsInvalid(): void
    {
        $this->client->request('GET', '/signup/nickname-exists', ['nick' => '']);
        $response = $this->client->getResponse();

        $this->assertSame(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('INVALID_REQUEST', $data['status']);
    }
}
