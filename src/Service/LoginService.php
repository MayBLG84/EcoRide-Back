<?php

namespace App\Service;

use App\DTO\LoginRequest;
use App\DTO\LoginResponse;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\Security\AntiBot\AntiBotService;
use App\Service\Security\Limiter\RateLimitService;
use App\Service\Security\SecurityService;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginService
{
    public function __construct(
        private UserRepository $userRepo,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager,
        private AntiBotService $antiBotService,
        private RateLimitService $rateLimit,
        private ValidatorInterface $validator,
        private SecurityService $security,
    ) {}

    public function login(LoginRequest $dto, string $ip): LoginResponse
    {
        $response = new LoginResponse;

        // ─────────────────────────────────────
        // Normalize
        // ─────────────────────────────────────
        $email = $this->security->normalizeEmail($dto->email);

        // ─────────────────────────────────────
        // Rate limit
        // ─────────────────────────────────────
        $key = hash('sha256', $email . '_' . $ip);

        if (!$this->rateLimit->consumeLimiter('login', $key)) {
            return $this->fail('TOO_MANY_ATTEMPTS');
        }

        // ─────────────────────────────────────
        // Anti bot
        // ─────────────────────────────────────
        if ($this->antiBotService->isHoneypotTriggered($dto->honeypot)) {
            return $this->fail('BOT_DETECTED');
        }
        if (!$this->antiBotService->verifyTurnstile($dto->turnstileToken)) {
            return $this->fail('INVALID_CAPTCHA');
        }

        // ─────────────────────────────────────
        // DTO Validation
        // ─────────────────────────────────────
        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            $response->status = 'INVALID_INPUT';
            foreach ($violations as $violation) {
                $response->errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            return $response;
        }

        // ─────────────────────────────────────
        // Find User
        // ─────────────────────────────────────
        try {
            $user = $this->userRepo->findOneByEmail($email);
        } catch (\Throwable) {
            return $this->fail('INTERNAL_ERROR');
        }

        // ─────────────────────────────────────
        // Verify credentials
        // ─────────────────────────────────────
        if (!$user || !$this->passwordHasher->isPasswordValid($user, $dto->password)) {
            return $this->fail('INVALID_CREDENTIALS');
        }

        // ─────────────────────────────────────
        // Reset rate limiter
        // ─────────────────────────────────────
        $this->rateLimit->resetLimiter('login', $key);

        // ─────────────────────────────────────
        // Generate JWT
        // ─────────────────────────────────────
        try {
            $token = $this->jwtManager->create($user);
        } catch (\Throwable) {
            return $this->fail('INTERNAL_ERROR');
        }

        // ─────────────────────────────────────
        // Success
        // ─────────────────────────────────────
        $response->status = 'SUCCESS';
        $response->userId = (string) $user->getId();
        $response->token = $token;
        $response->roles = $user->getRoles();
        return $response;
    }

    private function fail(string $status, array $errors = []): LoginResponse
    {
        $response = new LoginResponse();
        $response->status = $status;
        $response->errors = $errors;

        return $response;
    }
}
