<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Event\GuestEvent;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\ORM\Query\ResultSetMappingBuilder;
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

    public function getGuests(string $role): array
    {
        $cacheKey = 'guests_list_' . $role;

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($role) {
        $rsm = new ResultSetMappingBuilder($this->manager);
            $rsm->addRootEntityFromClassMetadata(User::class, 'u');

            $sql = 'SELECT ' . $rsm->generateSelectClause() . '
                        FROM "user" u
                        WHERE u.roles::text LIKE :role
                        AND u.roles::text NOT LIKE :admin
                        ORDER BY u.name ASC';

            $query = $this->manager->createNativeQuery($sql, $rsm);
            $query->setParameter('role', '%' . $role . '%');
            $query->setParameter('admin', '%ROLE_ADMIN%');

            $item->tag('guests');

            return $query->getResult();
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
