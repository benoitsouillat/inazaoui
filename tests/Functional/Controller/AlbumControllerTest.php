<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use App\Repository\AlbumRepository;
use App\Repository\UserRepository;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AlbumControllerTest extends WebTestCase
{
    private $client;
    private $router;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->router = $this->client->getContainer()->get('router');
    }
    public function testIfIndex(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $this->client->loginUser(
            $userRepository->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com'])
        );
        $this->client->request('GET', $this->router->generate('admin_album_index'));
        $this->assertResponseIsSuccessful();
        $albumRepository = static::getContainer()->get(AlbumRepository::class);
        $albumCount = count($albumRepository->findAll());

        $crawler = $this->client->getCrawler();
        $rows = $crawler->filter('table tbody tr');

        $this->assertCount($albumCount, $rows);
    }
    public function testIfUserHasNotAccessToAdminAlbum(): void
    {
        // On test en Anonyme
        $this->client->request('GET', $this->router->generate('admin_album_index'));
        self::assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        self::assertRouteSame('admin_login');

        // On connecte un utilisateur avec le rôle ROLE_USER
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'guest@localhost']);

        $this->client->loginUser($user);
        $this->client->request('GET', $this->router->generate('admin_album_index'));
        self::assertResponseStatusCodeSame(403);

        // On connecte un utilisateur avec le rôle ROLE_ADMIN
        $this->client->loginUser(
            $userRepository->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com'])
        );
        $this->client->request('GET', $this->router->generate('admin_album_index'));
        self::assertResponseIsSuccessful();
      }

    public function testUserAccessToCreateForm(): void
    {
        // On test en Anonyme
        $this->client->request('GET', $this->router->generate('admin_album_add'));
        self::assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        self::assertRouteSame('admin_login');

        // On connecte un utilisateur avec le rôle ROLE_ADMIN
        $userRepository = static::getContainer()->get(UserRepository::class);
        $adminUser = $userRepository->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com']);

        $this->client->loginUser($adminUser);
        $this->client->request('GET', $this->router->generate('admin_album_add'));
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Ajouter', [
            'album[name]' => 'New album'
        ]);
        self::assertResponseStatusCodeSame(201);
    }

    public function testUserAccessToEditForm(): void
    {

        $this->client->request('GET', $this->router->generate('admin_album_update', ['id' => 1]));
        self::assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        self::assertRouteSame('admin_login');

        $userRepository = static::getContainer()->get(UserRepository::class);
        $adminUser = $userRepository->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com']);

        $album = static::getContainer()->get(AlbumRepository::class)->findOneBy([]);

        $this->client->loginUser($adminUser);
        $this->client->request('GET', $this->router->generate('admin_album_update', ['id' => $album->getId()]));

        $this->client->submitForm('Modifier', [
            'album[name]' => 'Nouveau nom Album',
        ]);
        self::assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        self::assertRouteSame('admin_album_index');
    }

    public function testUserCanDeleteAlbum(): void
    {
        $album1 = new Album();
        $album1->setName("Test delete");
        $manager = static::getContainer()->get('doctrine')->getManager();
        $manager->persist($album1);
        $manager->flush();

        // Anonyme (Pas de suppression)
        $this->client->request('GET', $this->router->generate('admin_album_delete', ['id' => $album1->getId()]));
        self::assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        self::assertRouteSame('admin_login');

        // Guest (Pas de suppression)
        $userRepository = static::getContainer()->get(UserRepository::class);
        $guestUser = $userRepository->findOneBy(['email' => 'guest@localhost']);
        $this->client->loginUser($guestUser);

        $this->client->request('GET', $this->router->generate('admin_album_delete', ['id' => $album1->getId()]));
        self::assertResponseStatusCodeSame(403);

        // Administrateur
        $adminUser = $userRepository->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com']);
        $this->client->loginUser($adminUser);
        $this->client->request('GET', $this->router->generate('admin_album_delete', ['id' => $album1->getId()]));
        self::assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        self::assertRouteSame('admin_album_index');
    }

    public function testIfDeleteAlbumSettingMediaToNull(): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $adminUser = $userRepository->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com']);
        $this->client->loginUser($adminUser);

        $manager = static::getContainer()->get('doctrine')->getManager();

        $album = new Album();
        $album->setName("Test delete");
        $manager->persist($album);

        $media = new Media();
        $media->setTitle("Test Media");
        $media->setUser($adminUser);
        $media->setAlbum($album);
        $manager->persist($media);

        $manager->flush();

        $albumId = $album->getId();
        $mediaId = $media->getId();

        $this->client->request('GET', $this->router->generate('admin_album_delete', ['id' => $albumId]));
        self::assertResponseStatusCodeSame(302);

        $freshManager = static::getContainer()->get('doctrine')->getManager();
        $freshManager->clear();

        $freshMedia = $freshManager->getRepository(Media::class)->find($mediaId);

        self::assertNotNull($freshMedia);
        self::assertNull($freshMedia->getAlbum());

    }
}
