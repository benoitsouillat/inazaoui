<?php

namespace App\DataFixtures;

use App\Factory\MediaFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MediaFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        MediaFactory::createMany(10);
        $manager->flush();
    }
}
