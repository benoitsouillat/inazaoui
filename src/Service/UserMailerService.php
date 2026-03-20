<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class UserMailerService
{

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ParameterBagInterface $params
    )
    {
    }

    public function sendWelcomeEmail(User $guest): void
    {
        $resetToken = $this->resetPasswordHelper->generateResetToken($guest);
        $url = $this->urlGenerator->generate('app_reset_password', ['token' => $resetToken->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new TemplatedEmail())
            ->from($this->params->get('site_email'))
            ->to($guest->getEmail())
            ->subject("Bienvenue sur " . $this->params->get('site_name'))
            ->htmlTemplate('emails/user-created.html.twig')
            ->context([
                "user" => $guest,
                "setup_password_url" => $url,
            ]);
        $this->mailer->send($email);

    }
}
