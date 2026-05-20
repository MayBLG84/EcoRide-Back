<?php

namespace App\Service;

use App\DTO\UserSignupRequest;
use App\DTO\UserSignupResponse;
use App\Entity\User;
use App\Factory\UserAddressFactory;
use App\Factory\UserFactory;
use App\Repository\UserRepository;
use App\Service\Security\AntiBot\AntiBotService;
use App\Service\Security\Limiter\RateLimitService;
use App\Service\Images\ProfilePictureService;
use App\Service\Security\SecurityService;
use App\Resolver\UserRoleResolver;
use App\Service\Mailer\SignupMailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;

class SignupService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserFactory $userFactory,
        private readonly UserAddressFactory $addressFactory,
        private readonly UserRoleResolver $roleResolver,
        private readonly ProfilePictureService $profilePictureService,
        private readonly SignupMailerService $signupMailService,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $em,
        private readonly SecurityService $security,
        private readonly AntiBotService $antiBotService,
        private readonly RateLimitService $rateLimitService,
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger
    ) {}

    public function signup(UserSignupRequest $dto, string $ip): UserSignupResponse
    {

        $response = new UserSignupResponse();

        // ─────────────────────────────────────
        // Normalize
        // ─────────────────────────────────────
        $email = $this->security->normalizeEmail($dto->email);

        $nickname = $this->security->cleanNickname($dto->nickname);

        // ─────────────────────────────────────
        // Rate limit
        // ─────────────────────────────────────
        $key = hash('sha256', $email . '_' . $ip);

        if (!$this->rateLimitService->consumeLimiter('signup', $key)) {
            return $this->fail($response, 'TOO_MANY_ATTEMPTS');
        }

        // ─────────────────────────────────────
        // Anti bot
        // ─────────────────────────────────────
        if ($this->antiBotService->isHoneypotTriggered($dto->honeypot)) {
            return $this->fail($response, 'BOT_DETECTED');
        }

        if (!$this->antiBotService->verifyTurnstile($dto->turnstileToken)) {
            return $this->fail($response, 'INVALID_CAPTCHA');
        }

        // ─────────────────────────────────────
        // DTO Validation
        // ─────────────────────────────────────
        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {

            $response->status = 'INVALID_INPUT';

            foreach ($violations as $violation) {
                $response->errors[] =
                    $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }

            return $response;
        }

        // ─────────────────────────────────────
        // Business validations
        // ─────────────────────────────────────
        if (!$this->security->isValidName($dto->firstName)) {
            return $this->fail($response, 'INVALID_FIRST_NAME');
        }

        if (!$this->security->isValidName($dto->lastName)) {
            return $this->fail($response, 'INVALID_LAST_NAME');
        }

        if (!$this->security->isValidNickname($nickname)) {
            return $this->fail($response, 'INVALID_NICKNAME');
        }

        if (!$this->security->isValidEmail($email)) {
            return $this->fail($response, 'INVALID_EMAIL');
        }

        if (!$this->security->isValidTelephone($dto->telephone)) {
            return $this->fail($response, 'INVALID_TELEPHONE');
        }

        if (!$this->security->isAdult($dto->birthday)) {
            return $this->fail($response, 'UNDERAGE');
        }

        // ─────────────────────────────────────
        // Uniqueness checks
        // ─────────────────────────────────────
        try {

            if ($this->userRepository->existsByEmail($email)) {
                $response->status = 'EMAIL_ALREADY_EXISTS';
                return $response;
            }

            if ($this->userRepository->existsByNickname($nickname)) {
                $response->status = 'NICKNAME_ALREADY_EXISTS';
                return $response;
            }
        } catch (\Throwable) {

            return $this->fail($response, 'INTERNAL_ERROR');
        }

        // ─────────────────────────────────────
        // Create User entity
        // ─────────────────────────────────────
        try {

            // Resolve roles
            $roles = $this->roleResolver->resolve($dto->usageType);

            if (empty($roles)) {
                return $this->fail($response, 'INVALID_USAGE_TYPE');
            }

            // Create user
            $user = $this->userFactory->createFromSignup($dto, $roles);

            // Profile picture
            $pictureAttached = $this->profilePictureService->attachProfilePicture($user, $dto->profilePicture);

            if (!$pictureAttached) {
                return $this->fail($response, 'INVALID_PROFILE_PICTURE');
            }

            // Address
            if (!empty($dto->address)) {
                $address = $this->addressFactory->create($dto->address, $user);
                $user->setAddress($address);

                $this->em->persist($address);
            }

            // Persist
            $this->em->persist($user);
            $this->em->flush();
        } catch (\Throwable $e) {
            return $this->fail($response, 'INTERNAL_ERROR', [$e->getMessage()]);
        }

        // ─────────────────────────────────────
        // Send mail
        // ─────────────────────────────────────
        try {
            $this->signupMailService->sendWelcomeEmail($user);
        } catch (\Throwable $e) {
            $this->logger->error('Email failed', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage()
            ]);
        }

        // ─────────────────────────────────────
        // Success
        // ─────────────────────────────────────
        $response->status = 'SUCCESS';
        $response->id = $user->getId();
        $response->firstName = $user->getFirstName();
        $response->lastName = $user->getLastName();
        $response->nickname = $user->getNickname();
        $response->email = $user->getEmail();
        $response->credit = $user->getCredit();
        $response->createdAt = $user->getCreatedAt()->format(DATE_ATOM);

        return $response;
    }

    public function isNicknameExists(string $nickname): bool
    {

        $nickname = $this->security->cleanNickname($nickname);

        return $this->userRepository->findOneBy(['nickname' => $nickname]) !== null;
    }

    private function fail(UserSignupResponse $response, string $status, array $errors = []): UserSignupResponse
    {

        $response->status = $status;
        $response->errors = $errors;

        return $response;
    }
}
