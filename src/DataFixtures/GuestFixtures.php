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
        $this->cache->invalidateTags(['guests']);

        UserFactory::createMany(6);
        $manager->flush();
    }
}
