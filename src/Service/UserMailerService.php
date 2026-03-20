<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;

class UserMailerService
{

    public function __construct(
        private readonly MailerInterface $mailer
    )
    {
    }

    public function sendWelcomeEmail(User $guest, ParameterBagInterface $params): void
    {
        $email = (new TemplatedEmail())
            ->from($params->get('site_email'))
            ->to($guest->getEmail())
            ->subject("Bienvenue sur " . $params->get('site_name'))
            ->htmlTemplate('emails/user-created.html.twig')
            ->context([
                "user" => $guest,
            ]);
        $this->mailer->send($email);

    }
}
