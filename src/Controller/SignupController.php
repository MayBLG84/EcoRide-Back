<?php

namespace App\Controller;

use App\DTO\UserSignupRequest;
use App\Service\Mapper\SignupRequestMapper;
use App\Service\SignupService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SignupController extends AbstractController
{
    public function __construct(
        private readonly SignupService $signupService,
        private readonly SignupRequestMapper $mapper,
        private readonly ValidatorInterface $validator,
    ) {}

    #[Route('/api/users', name: 'api_user_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/users',
        summary: 'Create a new user',
        tags: ['Signup'],

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
                        'usageType',
                        'turnstileToken'
                    ],

                    properties: [

                        new OA\Property(
                            property: 'firstName',
                            type: 'string',
                            example: 'Jean'
                        ),

                        new OA\Property(
                            property: 'lastName',
                            type: 'string',
                            example: 'Dupont'
                        ),

                        new OA\Property(
                            property: 'nickname',
                            type: 'string',
                            example: 'jeand'
                        ),

                        new OA\Property(
                            property: 'email',
                            type: 'string',
                            format: 'email',
                            example: 'user@example.com'
                        ),

                        new OA\Property(
                            property: 'telephone',
                            type: 'string',
                            example: '0612345678'
                        ),

                        new OA\Property(
                            property: 'password',
                            type: 'string',
                            format: 'password',
                            example: 'Asdfg12345!'
                        ),

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
                            example: '{"street":"Rue X"}'
                        ),

                        new OA\Property(
                            property: 'photo',
                            type: 'string',
                            format: 'binary',
                            nullable: true
                        ),

                        new OA\Property(
                            property: 'honeypot',
                            type: 'string',
                            nullable: true,
                            example: ''
                        ),

                        new OA\Property(
                            property: 'turnstileToken',
                            type: 'string',
                            example: '0.xxxxxxxxx'
                        ),
                    ]
                )
            )
        ),

        responses: [

            new OA\Response(
                response: 201,
                description: 'User successfully created'
            ),

            new OA\Response(
                response: 400,
                description: 'Invalid request'
            ),

            new OA\Response(
                response: 409,
                description: 'Email or nickname already exists'
            ),

            new OA\Response(
                response: 500,
                description: 'Internal error'
            ),
        ]
    )]

    public function create(Request $request): Response
    {

        // ─────────────────────────────────────
        // Request → DTO
        // ─────────────────────────────────────
        $dto = $this->mapper->map($request);

        // ─────────────────────────────────────
        // Validation
        // ─────────────────────────────────────
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {

            $messages = [];

            foreach ($errors as $error) {
                $messages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }

            return $this->json(['status' => 'INVALID_REQUEST', 'errors' => $messages,], Response::HTTP_BAD_REQUEST);
        }

        // ─────────────────────────────────────
        // Service
        // ─────────────────────────────────────
        $response = $this->signupService->signup($dto, $request->getClientIp() ?? 'unknown');

        // ─────────────────────────────────────
        // HTTP Status
        // ─────────────────────────────────────
        $statusCode = match ($response->status) {

            'SUCCESS'
            => Response::HTTP_CREATED,

            'EMAIL_ALREADY_EXISTS',
            'NICKNAME_ALREADY_EXISTS'
            => Response::HTTP_CONFLICT,

            default
            => Response::HTTP_BAD_REQUEST,
        };

        return $this->json($response, $statusCode);
    }

    #[Route('/api/users/nickname-exists', name: 'api_user_nickname_exists', methods: ['GET'])]
    #[OA\Get(
        path: '/api/user/nickname-exists',
        summary: 'Check if nickname exists',
        tags: ['Signup'],

        parameters: [

            new OA\Parameter(
                name: 'nick',
                in: 'query',
                required: true,

                schema: new OA\Schema(
                    type: 'string'
                ),

                example: 'jeand'
            ),
        ],

        responses: [

            new OA\Response(
                response: 200,
                description: 'Nickname verification result'
            ),

            new OA\Response(
                response: 400,
                description: 'Invalid nickname'
            ),
        ]
    )]

    public function nicknameExists(Request $request): Response
    {

        $nick = trim((string) $request->query->get('nick', ''));

        if ($nick === '') {
            return $this->json(
                [
                    'status' => 'INVALID_REQUEST',
                    'message' => 'Nickname is required',
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->json(['exists' => $this->signupService->isNicknameExists($nick),]);
    }
}
