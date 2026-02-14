<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use OpenApi\Attributes as OA;

class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager,
        private HttpClientInterface $httpClient,
        private string $turnstileSecret,
        private RateLimiterFactory $loginLimiter,
        private RateLimiterFactory $blockLimiter
    ) {}

    #[OA\Post(
        path: "/api/login",
        summary: "User login",
        description: "Endpoint to authenticate a user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string"),
                    new OA\Property(property: "password", type: "string"),
                    new OA\Property(property: "honeypot", type: "string", nullable: true),
                    new OA\Property(property: "turnstileToken", type: "string", nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "SUCCESS"),
                        new OA\Property(property: "userId", type: "string"),
                        new OA\Property(property: "token", type: "string"),
                        new OA\Property(
                            property: "roles",
                            type: "array",
                            items: new OA\Items(type: "string")
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Invalid credentials",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "INVALID_INPUTS"),
                        new OA\Property(property: "message", type: "string")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Internal error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "INTERNAL_ERROR"),
                        new OA\Property(
                            property: "errors",
                            type: "array",
                            items: new OA\Items(type: "string")
                        )
                    ]
                )
            )
        ]
    )]
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? 'unknown';
        $ip = $request->getClientIp() ?? 'unknown';

        // Unique key (email + ip)
        $key = $email . '_' . $ip;

        // Honeypot check
        if (!empty($data['honeypot'])) {
            return new JsonResponse([
                'status' => 'ERROR_BOT',
                'message' => 'Bot detected'
            ], 400);
        }

        // Turnstile check
        if (empty($data['turnstileToken'])) {
            return new JsonResponse([
                'status' => 'ERROR_TURNSTILE',
                'message' => 'Turnstile token missing'
            ], 400);
        }

        try {
            $response = $this->httpClient->request('POST', 'https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'body' => http_build_query([
                    'secret' => $this->turnstileSecret,
                    'response' => $data['turnstileToken'],
                    'remoteip' => $request->getClientIp(),
                ]),
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ]
            ]);

            $content = $response->getContent();
            $result = json_decode($content, true);

            if (empty($result['success'])) {
                return new JsonResponse([
                    'status' => 'ERROR_TURNSTILE_VALIDATION',
                    'message' => 'Turnstile validation failed',
                    'cf_errors' => $result['error-codes'] ?? []
                ], 400);
            }
        } catch (\Throwable $e) {
            return new JsonResponse([
                'status' => 'INTERNAL_ERROR_CLOUDFLARE',
                'errors' => [$e->getMessage()]
            ], 500);
        }

        // Block limiter
        $block = $this->blockLimiter->create($key)->consume();

        if (!$block->isAccepted()) {
            return new JsonResponse([
                'status' => 'TOO_MANY_ATTEMPTS',
                'message' => 'Trop de tentatives de connexion. Veuillez réessayer dans quelques minutes.'
            ], 429);
        }

        // Login limiter
        $limit = $this->loginLimiter->create($key)->consume();

        if (!$limit->isAccepted()) {

            // Activate block limiter
            $this->blockLimiter->create($key)->consume(1);

            return new JsonResponse([
                'status' => 'TOO_MANY_ATTEMPTS',
                'message' => 'Trop de tentatives de connexion. Veuillez réessayer dans 10 minutes.'
            ], 429);
        }

        $user = $this->em->getRepository(User::class)
            ->findOneBy(['email' => $data['email'] ?? '']);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'] ?? '')) {
            return new JsonResponse([
                'status' => 'INVALID_INPUTS',
                'message' => 'Invalid email or password'
            ], 401);
        }

        // Reset limiters
        $this->loginLimiter->create($key)->reset();
        $this->blockLimiter->create($key)->reset();

        //Generate token
        $token = $this->jwtManager->create($user);

        return new JsonResponse([
            'status' => 'SUCCESS',
            'userId' => (string) $user->getId(),
            'token' => $token,
            'roles' => $user->getRoles()
        ], 200);
    }
}
