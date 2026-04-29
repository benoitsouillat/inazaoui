<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {}

    public function load(ObjectManager $manager): void
    {
        $guest = new User();
        $guest->setName('guest');
        $guest->setUsername('guest');
        $guest->setDescription('Guest is a user with limited access');
        $guest->setEmail('guest@localhost');
        $guest->setPassword($this->passwordHasher->hashPassword($guest, 'guest'));


        $admin = new User();
        $admin->setName('ina');
        $admin->setUsername('ina');
        $admin->setDescription('Ina is the admin user');
        $admin->setEmail('ina_zaoui@ina_zaoui.com');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password'));
        $admin->setRoles(['ROLE_ADMIN']);

        $manager->persist($guest);
        $manager->persist($admin);
        $manager->flush();
    }
}
