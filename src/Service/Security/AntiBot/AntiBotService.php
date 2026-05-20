<?php

namespace App\Service\Security\AntiBot;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class AntiBotService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $turnstileSecret
    ) {}

    public function isHoneypotTriggered(?string $honeypot): bool
    {
        return $honeypot !== null && trim($honeypot) !== '';
    }

    public function verifyTurnstile(
        ?string $token,
    ): bool {

        if (!$token || trim($token) === '') {
            return false;
        }

        try {

            $response = $this->httpClient->request(
                'POST',
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                [
                    'body' => [
                        'secret' => $this->turnstileSecret,
                        'response' => $token
                    ],
                ]
            );

            $data = $response->toArray(false);

            return ($data['success'] ?? false) === true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function validateTurnstile(?string $honeypot, ?string $turnstileToken): bool
    {
        if ($this->isHoneypotTriggered($honeypot)) {
            return false;
        }

        return $this->verifyTurnstile($turnstileToken);
    }
}
