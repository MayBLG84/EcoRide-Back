<?php

namespace App\Service;

use App\DTO\ContactRequest;
use App\DTO\ContactResponse;
use App\Document\Contact;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\Security\AntiBot\AntiBotService;
use App\Service\Security\Limiter\RateLimitService;
use App\Service\Security\SecurityService;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContactService
{
    public function __construct(
        private readonly DocumentManager $dm,
        private readonly SecurityService $security,
        private readonly AntiBotService $antiBotService,
        private readonly RateLimitService $rateLimitService,
        private readonly ValidatorInterface $validator,
    ) {}

    public function createContact(ContactRequest $dto, string $ip): ContactResponse
    {
        $response = new ContactResponse;

        // ─────────────────────────────────────
        // Rate limit
        // ─────────────────────────────────────
        $email = mb_strtolower(trim($dto->email));

        $key = hash('sha256', $email . '_' . $ip);

        if (!$this->rateLimitService->consumeLimiter('contact', $key)) {
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
                $response->errors[] = $violation->getPropertyPath()
                    . ': '
                    . $violation->getMessage();
            }

            return $response;
        }

        // ─────────────────────────────────────
        // Validations
        // ─────────────────────────────────────
        if (!$this->security->isValidName($dto->firstName)) {
            return $this->fail($response, 'INVALID_FIRST_NAME');
        }

        if (!$this->security->isValidName($dto->lastName)) {
            return $this->fail($response, 'INVALID_LAST_NAME');
        }

        if (!$this->security->isValidEmail($dto->email)) {
            return $this->fail($response, 'INVALID_EMAIL');
        }

        if (!$this->security->validateAttachments($dto->attachments)) {
            return $this->fail($response, 'INVALID_ATTACHMENTS');
        }

        // ─────────────────────────────────────
        // Sanitize
        // ─────────────────────────────────────
        $firstName = $this->security->sanitizeString($dto->firstName);
        $lastName = $this->security->sanitizeString($dto->lastName);
        $email = mb_strtolower(trim($dto->email));
        $reason = $this->security->sanitizeString($dto->reason);
        $detail = $this->security->sanitizeString($dto->detail);
        $description = $this->security->sanitizeString(
            $dto->description,
            1000
        );
        $rideId = $dto->rideId
            ? $this->security->sanitizeString($dto->rideId)
            : null;

        // ─────────────────────────────────────
        // Create document
        // ─────────────────────────────────────
        $contact = new Contact();

        $contact->setFirstName($firstName);
        $contact->setLastName($lastName);
        $contact->setEmail($email);
        $contact->setReason($reason);
        $contact->setDetail($detail);
        $contact->setDescription($description);
        $contact->setRideId($rideId);

        // ─────────────────────────────────────
        // Upload attachments
        // ─────────────────────────────────────
        $uploadDir = __DIR__ . '/../../var/uploads/contact';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach ($dto->attachments as $file) {

            if (!$file instanceof UploadedFile) {
                continue;
            }

            $safeFilename = uniqid() . '.' . $file->guessExtension();

            try {
                $file->move($uploadDir, $safeFilename);
                $contact->addAttachment($safeFilename);
            } catch (\Exception $e) {
                $response->status = 'UPLOAD_ERROR';
                $response->errors = [$e->getMessage()];

                return $response;
            }
        }

        // ─────────────────────────────────────
        // Persist
        // ─────────────────────────────────────
        try {

            $this->dm->persist($contact);
            $this->dm->flush();
        } catch (\Throwable $e) {

            $response->status = 'INTERNAL_ERROR';
            $response->errors = [$e->getMessage()];

            return $response;
        }

        // ─────────────────────────────────────
        // Success
        // ─────────────────────────────────────
        $response->status = 'SUCCESS';
        $response->id = $contact->getId();
        $response->createdAt = $contact
            ->getCreatedAt()
            ->format(DATE_ATOM);

        return $response;
    }

    private function fail(
        ContactResponse $response,
        string $status
    ): ContactResponse {

        $response->status = $status;

        return $response;
    }
}
