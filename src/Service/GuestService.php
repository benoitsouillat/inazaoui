<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Event\GuestEvent;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class GuestService
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly TagAwareCacheInterface $cache
    )
    {}

    /**
     * Get All Guest with this Role
     * @param string $role
     * @return array
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getGuests(string $role): array
    {
        $cacheKey = 'guests_list_' . $role;
        $userRepository = $this->manager->getRepository(User::class);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($role, $userRepository) {
            $item->tag('guests');

            return $userRepository->getUsersNotAdmin($role);
        });
    }

    /**
     * Get Active Guests where Not Admin
     * @return array
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getActiveGuests(): array
    {
        $cacheKey = 'guests_active_list';
        $userRepository = $this->manager->getRepository(User::class);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($userRepository) {
            $item->tag('guests');

            return $userRepository->getActiveGuestsNotAdmin();
        });
    }

    public function addUser(User $guest): User| \Exception
    {
        $guest->setRoles(['ROLE_USER']);
        $plainPassword = bin2hex(random_bytes(16));
        $hashedPassword = $this->passwordHasher->hashPassword($guest, $plainPassword);

        $guest->setPassword($hashedPassword);

        $this->manager->persist($guest);
        $this->manager->flush();
        $this->dispatcher->dispatch(new GuestEvent($guest), GuestEvent::GUEST_CREATED);

        return $guest;
    }

    public function editUser(User $guest): User| \Exception
    {
        $this->dispatcher->dispatch(new GuestEvent($guest), GuestEvent::GUEST_EDITED);
        $this->manager->persist($guest);
        $this->manager->flush();

        return $guest;
    }
}
