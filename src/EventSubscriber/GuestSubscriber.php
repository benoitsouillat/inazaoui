<?php

declare(strict_types=1);

namespace App\EventSubscriber;

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
        $guest = $event->getGuest();

        // Envoie un email de bienvenue à l'invité
        $this->mailerService->sendWelcomeEmail($guest);
    }

    public function onGuestDeleted(GuestEvent $event): void
    {
        $guest = $event->getGuest();
        $medias = $guest->getMedias();
        foreach ($medias as $media) {

            // unlink($media->getPath()); /* Les médias sont supprimés par Vich_uploader
            $this->manager->remove($media);
        }
        $this->manager->remove($guest);
        $this->manager->flush();
    }

    public function cleanGuestCache(GuestEvent $event): void
    {
        $this->cache->invalidateTags(['guests']);
    }
}
