<?php

namespace App\DataFixtures;

use App\Factory\AlbumFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AlbumFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        AlbumFactory::createMany(8);
        $manager->flush();
    }
}
