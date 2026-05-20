<?php

namespace App\Service\Mailer;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class SignupMailerService
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function sendWelcomeEmail(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@ecoride.fr', 'EcoRide'))
            ->to($user->getEmail())
            ->subject('Bienvenue sur EcoRide')
            ->htmlTemplate('mail/signup-success.html.twig')
            ->context([
                'user' => $user,
                'giftAmount' => 20
            ]);

        $this->mailer->send($email);
    }
}
