<?php

namespace App\Repository;

use App\Entity\Media;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    // Pour permettre la connexion avec le username ou l'email
    public function loadUserByIdentifier(string $identifier): ?User
    {
        $entityManager = $this->getEntityManager();

        return $entityManager->createQuery(
            'SELECT u
                FROM App\Entity\User u
                WHERE u.username = :query
                OR u.email = :query'
        )
            ->setParameter('query', $identifier)
            ->getOneOrNullResult();
    }

    // Retourne tous les utilisateurs non-administrateurs ayant ce rôle
    public function getUsersNotAdmin($role = 'ROLE_USER'): array
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(User::class, 'u');

        $sql = 'SELECT ' . $rsm->generateSelectClause() . '
                        FROM "user" u
                        WHERE u.roles::text LIKE :role
                        AND u.roles::text NOT LIKE :admin
                        ORDER BY u.name ASC';

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('role', '%' . $role . '%');
        $query->setParameter('admin', '%ROLE_ADMIN%');

        return $query->getResult();
    }

    // Retourne tous les guests
    public function getActiveGuestsNotAdmin(): array
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(User::class, 'u');
        $rsm->addScalarResult('media_count', 'mediaCount');

        $sql = 'SELECT ' . $rsm->generateSelectClause() . ', COUNT(m.id) as media_count
                    FROM "user" u
                    LEFT JOIN "media" m ON u.id = m.user_id
                    WHERE u.active = true
                    AND u.roles::text NOT LIKE :admin
                    GROUP BY u.id
                    ORDER BY u.name ASC';

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('admin', '%ROLE_ADMIN%');

        return $query->getResult();
    }
}
