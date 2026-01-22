<?php

namespace App\Controller;

use App\DTO\UserSignupRequest;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use OpenApi\Attributes as OA;

final class SignupController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('/api/user/create', name: 'api_user_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/user/create',
        summary: 'Create a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: [
                        'firstName',
                        'lastName',
                        'nickname',
                        'email',
                        'telephone',
                        'password',
                        'birthday',
                        'usageType'
                    ],
                    properties: [
                        new OA\Property(property: 'firstName', type: 'string', example: 'Jean'),
                        new OA\Property(property: 'lastName', type: 'string', example: 'Dupont'),
                        new OA\Property(property: 'nickname', type: 'string', example: 'jeand'),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                        new OA\Property(property: 'telephone', type: 'string', example: '0612345678'),
                        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Asdfg12345!'),
                        new OA\Property(
                            property: 'birthday',
                            type: 'string',
                            format: 'date',
                            example: '1995-06-15'
                        ),
                        new OA\Property(
                            property: 'usageType',
                            type: 'string',
                            example: 'BOTH'
                        ),
                        new OA\Property(
                            property: 'address',
                            type: 'string',
                            nullable: true,
                            description: 'JSON string of address object',
                            example: '{"street":"Rue X","number":"12","city":"Paris"}'
                        ),
                        new OA\Property(
                            property: 'photo',
                            type: 'string',
                            format: 'binary',
                            nullable: true,
                            description: 'Optional profile picture'
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User successfully created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'SUCCESS'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 42),
                                new OA\Property(property: 'firstName', type: 'string', example: 'John'),
                                new OA\Property(property: 'lastName', type: 'string', example: 'Doe'),
                                new OA\Property(property: 'nickname', type: 'string', example: 'jeand64@'),
                                new OA\Property(property: 'email', type: 'string', example: 'j-doe64@test.com'),
                                new OA\Property(property: 'credit', type: 'float', example: 20.00),
                                new OA\Property(property: 'createdAt', type: 'string', example: '2026-01-22')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: 'Conflict: email or nickname already exists',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'status',
                            type: 'string',
                            example: 'EMAIL_ALREADY_EXISTS',
                            enum: ['EMAIL_ALREADY_EXISTS', 'NICKNAME_ALREADY_EXISTS']
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid request payload',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'INVALID_REQUEST'),
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            )
        ]
    )]
    public function create(Request $request): Response
    {
        $dto = $this->mapRequestToDto($request);
        $response = $this->userService->createUser($dto);
        $statusCode = match ($response->status) {
            'SUCCESS' => Response::HTTP_CREATED,
            'EMAIL_ALREADY_EXISTS',
            'NICKNAME_ALREADY_EXISTS' => Response::HTTP_CONFLICT,
            default => Response::HTTP_BAD_REQUEST,
        };
        return $this->json($response, $statusCode);
    }

    #[Route('/api/users/nickname-exists', name: 'api_user_nickname_exists', methods: ['GET'])]
    #[OA\Get(
        path: '/api/users/nickname-exists',
        summary: 'Check if a nickname already exists',
        parameters: [
            new OA\Parameter(
                name: 'nick',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string'),
                example: 'jeand'
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Verification result',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'SUCCESS'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'exists', type: 'boolean', example: true)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid or missing nickname parameter',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'INVALID_REQUEST'),
                        new OA\Property(property: 'message', type: 'string')
                    ]
                )
            )
        ]
    )]
    public function nicknameExists(Request $request): Response
    {
        $nick = trim((string) $request->query->get('nick'));

        if ($nick === '') {
            return $this->json(
                ['status' => 'INVALID_REQUEST', 'message' => 'Nickname is required'],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->json([
            'exists' => $this->userService->isNicknameExists($nick)
        ]);
    }

    private function mapRequestToDto(Request $request): UserSignupRequest
    {
        $dto = new UserSignupRequest();
        $dto->firstName = (string) $request->request->get('firstName', '');
        $dto->lastName = (string) $request->request->get('lastName', '');
        $dto->nickname = (string) $request->request->get('nickname', '');
        $dto->email = (string) $request->request->get('email', '');
        $dto->telephone = (string) $request->request->get('telephone', '');
        $dto->password = (string) $request->request->get('password', '');
        $dto->usageType = (string) $request->request->get('usageType', '');

        $birthdayRaw = $request->request->get('birthday');
        if (!$birthdayRaw) {
            throw new BadRequestHttpException('INVALID_BIRTHDAY');
        }

        try {
            $dto->birthday = new \DateTimeImmutable($birthdayRaw);
        } catch (\Throwable) {
            throw new BadRequestHttpException('INVALID_BIRTHDAY_FORMAT');
        }

        $addressJson = $request->request->get('address');
        $dto->address = $addressJson ? json_decode($addressJson, true) : null;

        $dto->profilePicture = $request->files->get('photo');

        return $dto;
    }
}
