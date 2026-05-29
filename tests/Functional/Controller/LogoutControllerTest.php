<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LogoutControllerTest extends WebTestCase
{
    public function testLogout(): void
    {
        $client = static::createClient();

        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $user = $em->getRepository(User::class)->findOneBy([]);

        $client->loginUser($user);
        $client->request('GET', '/logout');

        $this->assertNull($client->getContainer()->get('security.token_storage')->getToken());
    }
}
