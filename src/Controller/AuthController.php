<?php

namespace App\Controller;

use App\DTO\LoginRequest;
use App\Service\LoginService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    #[Route('/api/login', methods: ['POST'])]
    #[OA\Post(
        path: '/api/login',
        summary: 'Authenticate a user',
        tags: ['Authentication'],

        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password', 'turnstileToken'],
                properties: [

                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'jean.dupont@email.com'
                    ),

                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'MySecurePassword123'
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
                        example: '0.xxxxxxxxxxxxxxxxx'
                    ),
                ]
            )
        ),

        responses: [

            new OA\Response(
                response: 200,
                description: 'Operation processed',
                content: new OA\JsonContent(
                    properties: [

                        new OA\Property(
                            property: 'status',
                            type: 'string',
                            example: 'SUCCESS',
                            enum: [
                                'SUCCESS',
                                'INVALID_CREDENTIALS',
                                'INVALID_CAPTCHA',
                                'BOT_DETECTED',
                                'TOO_MANY_ATTEMPTS',
                                'INVALID_INPUT',
                                'INTERNAL_ERROR'
                            ]
                        ),

                        new OA\Property(
                            property: 'userId',
                            type: 'string',
                            nullable: true,
                            example: '682a1e51f0c123456789abcd'
                        ),

                        new OA\Property(
                            property: 'token',
                            type: 'string',
                            nullable: true,
                            example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'
                        ),

                        new OA\Property(
                            property: 'roles',
                            type: 'array',
                            nullable: true,
                            items: new OA\Items(type: 'string'),
                            example: ['ROLE_PASSENGER']
                        ),

                        new OA\Property(
                            property: 'errors',
                            type: 'array',
                            nullable: true,
                            items: new OA\Items(type: 'string'),
                            example: [
                                'email: This value is not a valid email address.'
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function login(Request $request, LoginService $loginService,): JsonResponse
    {

        // ─────────────────────────────────────
        // Decode JSON
        // ─────────────────────────────────────
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            $data = [];
        }

        // ─────────────────────────────────────
        // Map Request → DTO
        // ─────────────────────────────────────
        $dto = new LoginRequest();

        $dto->email = (string) ($data['email'] ?? '');

        $dto->password = (string) ($data['password'] ?? '');

        $dto->honeypot = isset($data['honeypot'])
            ? (string) $data['honeypot']
            : null;

        $dto->turnstileToken = (string) ($data['turnstileToken'] ?? '');

        // ─────────────────────────────────────
        // Service
        // ─────────────────────────────────────
        $response = $loginService->login(
            $dto,
            $request->getClientIp() ?? 'unknown'
        );

        // ─────────────────────────────────────
        // Always HTTP 200
        // Front handles response.status
        // ─────────────────────────────────────
        return $this->json($response, 200);
    }
}
