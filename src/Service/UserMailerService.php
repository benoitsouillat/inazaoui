<?php

declare(strict_types=1);

namespace App\Service;

class UserMailerService
{

    public function __construct()
    {
    }

    public function sendWelcomeEmail($guest): void
    {
        // Logique pour envoyer un email de bienvenue à l'invité
        // Par exemple, utiliser SwiftMailer ou Symfony Mailer pour envoyer l'email
        // Vous pouvez personnaliser le contenu de l'email en fonction des informations de l'invité
    }
}
