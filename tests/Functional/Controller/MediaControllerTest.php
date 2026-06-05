<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Media;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MediaControllerTest extends WebTestCase
{
    private $client;
    private $router;

    public function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->router = $this->client->getContainer()->get('router');
    }

    public function tearDown(): void
    {
        $manager = $this->client->getContainer()->get('doctrine')->getManager();
        $mediaRepository = $this->client->getContainer()->get(MediaRepository::class);

        foreach (['Media test ajouté', 'Media test à supprimer'] as $title) {
            foreach ($mediaRepository->findBy(['title' => $title]) as $media) {
                $manager->remove($media);
            }
        }
        $manager->flush();

        parent::tearDown();
    }

    public function testIfIndex(): void
    {
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $this->client->loginUser(
            $userRepository->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com'])
        );

        $this->client->request('GET', $this->router->generate('admin_media_index'));
        $this->assertResponseIsSuccessful();

        $mediaCount = count(
            $this->client->getContainer()->get(MediaRepository::class)->findAll()
        );

        $rows = $this->client->getCrawler()->filter('table tbody tr');
        $this->assertCount($mediaCount, $rows);
    }

    public function testIfUserHasAccessToAdminMedia(): void
    {
        // Anonyme
        $this->client->request('GET', $this->router->generate('admin_media_index'));
        self::assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        self::assertRouteSame('admin_login');

        $userRepository = $this->client->getContainer()->get(UserRepository::class);

        // ROLE_USER
        $this->client->loginUser($userRepository->findOneBy(['email' => 'guest@localhost']));
        $this->client->request('GET', $this->router->generate('admin_media_index'));
        self::assertResponseStatusCodeSame(200);

        // ROLE_ADMIN
        $this->client->loginUser($userRepository->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com']));
        $this->client->request('GET', $this->router->generate('admin_media_index'));
        self::assertResponseIsSuccessful();
    }

    public function testUserAccessToAddForm(): void
    {
        // Anonyme
        $this->client->request('GET', $this->router->generate('admin_media_add'));
        self::assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        self::assertRouteSame('admin_login');

        $userRepository = $this->client->getContainer()->get(UserRepository::class);

        // ROLE_USER
        $this->client->loginUser($userRepository->findOneBy(['email' => 'guest@localhost']));
        $this->client->request('GET', $this->router->generate('admin_media_add'));
        self::assertResponseStatusCodeSame(200);

        // ROLE_ADMIN — GET
        $adminUser = $userRepository->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com']);
        $this->client->loginUser($adminUser);
        $this->client->request('GET', $this->router->generate('admin_media_add'));
        self::assertResponseIsSuccessful();

        // ROLE_ADMIN — POST (sans fichier : ?File est nullable, aucune contrainte NotNull)
        $this->client->submitForm('Ajouter', [
            'media[title]' => 'Media test ajouté',
            'media[user]'  => $adminUser->getId(),
        ]);
        self::assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        self::assertRouteSame('admin_media_index');
    }

    public function testGuestCanDeleteMedia(): void
    {
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $manager = $this->client->getContainer()->get('doctrine')->getManager();
        $guestUser = $userRepository->findOneBy(['email' => 'guest@localhost']);

        $media = new Media();
        $media->setTitle('Media test à supprimer')
            ->setUser($guestUser);
        $manager->persist($media);
        $manager->flush();

        // Anonyme
        $this->client->request('GET', $this->router->generate('admin_media_delete', ['id' => $media->getId()]));
        self::assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        self::assertRouteSame('admin_login');

        // ROLE_USER
        $this->client->loginUser($guestUser);
        $this->client->request('GET', $this->router->generate('admin_media_delete', ['id' => $media->getId()]));
        self::assertResponseStatusCodeSame(302);

    }
    public function testAdminCanDeleteMedia(): void
    {
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $manager = $this->client->getContainer()->get('doctrine')->getManager();
        $adminUser = $userRepository->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com']);

        $media = new Media();
        $media->setTitle('Media test à supprimer');
        $media->setUser($adminUser);
        $manager->persist($media);
        $manager->flush();

        // ROLE_ADMIN
        $this->client->loginUser($adminUser);
        $this->client->request('GET', $this->router->generate('admin_media_delete', ['id' => $media->getId()]));
        self::assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        self::assertRouteSame('admin_media_index');
    }
    public function testUserCanNotDeleteMediaOfOtherUsers(): void
    {
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $manager = $this->client->getContainer()->get('doctrine')->getManager();
        $guestUser = $userRepository->findOneBy(['email' => 'guest@localhost']);
        $adminUser = $userRepository->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com']);

        $media = new Media();
        $media->setTitle('Media test à supprimer');
        $media->setUser($adminUser);
        $manager->persist($media);
        $manager->flush();

        // ROLE_USER dont le média ne lui appartient pas
        $this->client->loginUser($guestUser);
        $this->client->request('GET', $this->router->generate('admin_media_delete', ['id' => $media->getId()]));
        self::assertResponseStatusCodeSame(403);
    }
}
