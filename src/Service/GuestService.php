<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Event\GuestEvent;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class GuestService
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EventDispatcherInterface $dispatcher
    )
    {}

    public function getUsers(): array
    {
        return $this->manager->getRepository(User::class)->findAll();
    }

    public function addUser(User $guest): ?User
    {
        try {
            $guest->setRoles(['ROLE_USER']);
            $plainPassword = bin2hex(random_bytes(16));
            $hashedPassword = $this->passwordHasher->hashPassword($guest, $plainPassword);

            $guest->setPassword($hashedPassword);

            $event = new GuestEvent($guest);
            $this->dispatcher->dispatch($event, GuestEvent::GUEST_CREATED);

            $this->manager->persist($guest);
            $this->manager->flush();
            return $guest;
        }
        catch (\Exception $exception) {
            return null;
        }
    }
}
