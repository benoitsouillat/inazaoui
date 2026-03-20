<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\GuestEvent;
use App\Service\UserMailerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class GuestSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private readonly UserMailerService $mailerService,
        private readonly TagAwareCacheInterface $cache
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            GuestEvent::GUEST_CREATED => [
                    ['cleanGuestCache', 10],
                    ['onGuestCreated', 0],
                ],
            GuestEvent::GUEST_EDITED => [
                ['cleanGuestCache', 10]
            ],
        ];
    }

    public function onGuestCreated(GuestEvent $event): void
    {
        $guest = $event->getGuest();
        // Envoyer un email de bienvenue à l'invité
        $this->mailerService->sendWelcomeEmail($guest);
    }

    public function cleanGuestCache(GuestEvent $event): void
    {
        $this->cache->invalidateTags(['guests']);
    }
}
