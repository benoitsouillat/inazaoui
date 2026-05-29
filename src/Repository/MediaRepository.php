<?php

namespace App\Repository;

use App\Entity\Album;
use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Media>
 *
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    public function findActiveByAlbum(Album $album)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.album = :album')
            ->setParameter('album', $album)
            ->join('m.user', 'u')
            ->andWhere('u.active = true')
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countAllByCriteria(array $criteria)
    {
        $queryBuilder = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)');

        if (!empty($criteria)) {
            $queryBuilder->join('m.user', 'u')
                ->andWhere('u = :user')
                ->setParameter('user', $criteria['user']);
        }


        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function countAllActive()
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->join('m.user', 'u')
            ->andWhere('u.active = true')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAllActiveByPage($page = 1, $limit = 24)
    {
        return $this->createQueryBuilder('m')
            ->join('m.user', 'u')
            ->andWhere('u.active = true')
            ->orderBy('m.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

}
