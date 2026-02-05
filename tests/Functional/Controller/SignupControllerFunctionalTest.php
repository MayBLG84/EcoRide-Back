<?php

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\ApiTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\User;

class SignupControllerFunctionalTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testSuccessfulUserCreation(): void
    {
        $em = $this->em;

        // Dummy photo for upload
        $photo = new UploadedFile(
            __DIR__ . '/../fixtures/test-photo.jpg',
            'test-photo.jpg',
            'image/jpeg',
            null,
            true
        );

        $payload = [
            'firstName' => 'Test',
            'lastName' => 'User',
            'nickname' => 'testuser',
            'email' => 'testuser@example.com',
            'telephone' => '0123456789',
            'password' => 'StrongPass123!',
            'birthday' => '1990-01-01',
            'usageType' => 'BOTH',
            'address' => [
                'street' => 'Rue de Test',
                'number' => '12',
                'city' => 'Paris',
                'zipcode' => '75001',
                'country' => 'France'
            ],
            'photo' => $photo,
        ];

        $this->client->catchExceptions(true);
        $this->client->request('POST', '/api/user/create', $payload);

        $response = $this->client->getResponse();
        $responseContent = $response->getContent();
        var_dump($responseContent);

        $this->assertEquals(201, $response->getStatusCode(), 'User should be created successfully');

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('SUCCESS', $data['status']);
        $this->assertArrayHasKey('id', $data);
        $userId = $data['id'];

        /** @var User $user */
        $user = $em->getRepository(User::class)->find($userId);

        $this->assertNotNull($user, 'User entity should exist in the database');
        $this->assertEquals('testuser', $user->getNickname());
        $this->assertEquals('Test', $user->getFirstName());
        $this->assertEquals('User', $user->getLastName());
        $this->assertEquals('testuser@example.com', $user->getEmail());
        $this->assertEquals('0123456789', $user->getTelephone());
        $this->assertNotNull($user->getAddress());
        $this->assertEquals('Rue de Test', $user->getAddress()->getStreet());
        $this->assertEquals('12', $user->getAddress()->getNumber());
        $this->assertEquals('Paris', $user->getAddress()->getCity());
        $this->assertEquals('75001', $user->getAddress()->getZipcode());
        $this->assertEquals('France', $user->getAddress()->getCountry());
    }

    public function testConflictOnDuplicateEmailOrNickname(): void
    {
        $payload = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'nickname' => 'JDoe',
            'email' => 'john.doe@test.com',
            'telephone' => '0123456789',
            'password' => 'StrongPass123!',
            'birthday' => '1990-01-01',
            'usageType' => 'BOTH'
        ];

        $this->client->request('POST', '/api/user/create', $payload);

        $response = $this->client->getResponse();
        $this->assertEquals(409, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertContains($data['status'], ['EMAIL_ALREADY_EXISTS', 'NICKNAME_ALREADY_EXISTS']);
    }

    public function testInvalidRequestPayload(): void
    {
        $this->client->catchExceptions(true);

        $payload = [
            'firstName' => '',
            'lastName' => '',
            'nickname' => '',
            'email' => 'invalid-email',
            'telephone' => 'abc',
            'password' => '',
            'birthday' => 'not-a-date',
            'usageType' => ''
        ];

        $this->client->request('POST', '/api/user/create', $payload);

        $response = $this->client->getResponse();

        $responseContent = $response->getContent();
        var_dump($responseContent);

        $this->assertEquals(400, $response->getStatusCode());

        $data = json_decode($responseContent, true);
        $this->assertIsArray($data, 'Response should be JSON');
        $this->assertEquals('INVALID_REQUEST', $data['status']);
        $this->assertNotEmpty($data['errors']);
    }

    public function testNicknameExistsEndpoint(): void
    {
        // Existing nickname
        $this->client->request('GET', '/api/users/nickname-exists', ['nick' => 'JDoe']);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['exists']);

        // Non-existing nickname
        $this->client->request('GET', '/api/users/nickname-exists', ['nick' => 'NonExistentNick']);
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['exists']);

        // Missing nickname
        $this->client->request('GET', '/api/users/nickname-exists', []);
        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('INVALID_REQUEST', $data['status']);
    }
}
