<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\Entity\Media;
use App\Entity\User;
use App\Event\GuestEvent;
use App\EventSubscriber\GuestSubscriber;
use App\Service\UserMailerService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class GuestSubscriberTest extends TestCase {
    private UserMailerService|MockObject $mailer;
    private TagAwareCacheInterface|MockObject $cache;
    private EntityManagerInterface|MockObject $manager;
    private GuestSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->mailer = $this->createMock(UserMailerService::class);
        $this->cache = $this->createMock(TagAwareCacheInterface::class);
        $this->manager = $this->createMock(EntityManagerInterface::class);

        $this->subscriber = new GuestSubscriber(
            $this->mailer,
            $this->cache,
            $this->manager
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $events = GuestSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(GuestEvent::GUEST_CREATED, $events);
        $this->assertArrayHasKey(GuestEvent::GUEST_EDITED, $events);
        $this->assertArrayHasKey(GuestEvent::GUEST_DELETED, $events);
    }

    public function testOnGuestCreatedSendsEmail(): void
    {
        $user = new User();
        $event = new GuestEvent($user);

        $this->mailer->expects($this->once())
            ->method('sendWelcomeEmail')
            ->with($user);

        $this->subscriber->onGuestCreated($event);
    }

    public function testCleanGuestCache(): void
    {
        $event = new GuestEvent(new User());

        $this->cache->expects($this->once())
            ->method('invalidateTags')
            ->with(['guests']);

        $this->subscriber->cleanGuestCache($event);
    }

    public function testOnGuestDeletedRemovesMedias(): void
    {
        $user = new User();
        $media = new Media();

        $user->setMedias(new ArrayCollection([$media]));
        $event = new GuestEvent($user);

        $this->manager->expects($this->once())->method('remove')->with($media);
        $this->manager->expects($this->never())->method('flush');

        $this->subscriber->onGuestDeleted($event);
    }
}
