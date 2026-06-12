<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Event\GuestEvent;
use App\Service\UserMailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class GuestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserMailerService $mailerService,
        private readonly TagAwareCacheInterface $cache,
        private readonly EntityManagerInterface $manager,
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
            GuestEvent::GUEST_DELETED => [
                ['onGuestDeleted', 10],
                ['cleanGuestCache', 9],
            ]
        ];
    }

    public function onGuestCreated(GuestEvent $event): void
    {
        /** @var User $guest */
        $guest = $event->getGuest();

        // Envoie un email de bienvenue à l'invité
        $this->mailerService->sendWelcomeEmail($guest);
    }

    public function onGuestDeleted(GuestEvent $event): void
    {
        /** @var User $guest */
        $guest = $event->getGuest();
        foreach ($guest->getMedias() as $media) {
            // unlink($media->getPath()); /* Les médias sont supprimés par Vich_uploader
            $this->manager->remove($media);
        }
    }

    public function cleanGuestCache(): void
    {
        $this->cache->invalidateTags(['guests']);
    }
}
