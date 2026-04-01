<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class GuestFixtures extends Fixture
{
    public function __construct(
        private readonly TagAwareCacheInterface $cache
    ) {}

    public function load(ObjectManager $manager): void
    {
        /**
         * BEST PRACTICE : Vider le cache avec make pour être sûr d'avoir une base neuve
         * Permet de mieux séparer les responsabilités
         */
        $this->cache->invalidateTags(['guests']);

        UserFactory::createMany(6);
        $manager->flush();
    }
}
