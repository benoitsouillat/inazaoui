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
        ];
    }

    public function onGuestCreated($event): void
    {
        $guest = $event->getGuest();
        // Envoyer un email de bienvenue à l'invité
        $this->mailerService->sendWelcomeEmail($guest);
    }
}
