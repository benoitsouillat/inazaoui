<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Service\UserMailerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GuestCreatedSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private readonly UserMailerService $mailerService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            'guest.created' => 'onGuestCreated',
            'guest.edited' => 'onGuestEdited',
        ];
    }

    public function onGuestCreated($event): void
    {
        // Vider le cache des invités

        $guest = $event->getGuest();
        // Envoyer un email de bienvenue à l'invité
        $this->mailerService->sendWelcomeEmail($guest);
    }

    public function onGuestEdited($event): void
    {
        // Vider le cache des invités
    }
}
