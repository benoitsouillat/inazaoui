<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Entity\Media;
use App\Repository\UserRepository;
use App\Service\GuestService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GuestControllerTest extends WebTestCase
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
        $this->client->enableReboot();

        $container = $this->client->getContainer();
        $user = $container->get(UserRepository::class)->findOneBy(['email' => 'newguest@localhost']);
        if ($user) {
            $container->get(GuestService::class)->deleteUser($user);
        }
        parent::tearDown();
    }

    public function testIfIndex(): void
    {
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $this->client->loginUser(
            $userRepository->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com'])
        );
        $this->client->request('GET', $this->router->generate('admin_guest_index'));
        $this->assertResponseIsSuccessful();
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $userCount = count($userRepository->getUsersNotAdmin());

        $crawler = $this->client->getCrawler();
        $rows = $crawler->filter('table tbody tr');

        $this->assertCount($userCount, $rows);
    }

    public function testIfUserHasNotAccessToAdminGuest(): void
    {
        // On test en Anonyme
        $this->client->request('GET', $this->router->generate('admin_guest_index'));
        self::assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        self::assertRouteSame('admin_login');

        // On connecte un utilisateur avec le rôle ROLE_USER
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'guest@localhost']);

        $this->client->loginUser($user);
        $this->client->request('GET', $this->router->generate('admin_guest_index'));
        self::assertResponseStatusCodeSame(403);

        // On connecte un utilisateur avec le rôle ROLE_ADMIN
        $this->client->loginUser(
            $userRepository->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com'])
        );
        $this->client->request('GET', $this->router->generate('admin_guest_index'));
        self::assertResponseIsSuccessful();
      }

    public function testUserAccessToCreateForm(): void
    {
        // On test en Anonyme
        $this->client->request('GET', $this->router->generate('admin_guest_add'));
        self::assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        self::assertRouteSame('admin_login');

        // On connecte un utilisateur avec le rôle ROLE_ADMIN
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $adminUser = $userRepository->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com']);

        $this->client->loginUser($adminUser);
        $this->client->request('GET', $this->router->generate('admin_guest_add'));
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Ajouter', [
            'guest[name]' => 'Nouvel utilisateur',
            'guest[username]' => 'Nouvel_utilisateur',
            'guest[email]' => 'newguest@localhost',
            'guest[description]' => 'Description du nouvel utilisateur',
        ]);
        self::assertResponseStatusCodeSame(201);
    }

    public function testUserAccessToEditForm(): void
    {
        $guest = $this->client->getContainer()->get(UserRepository::class)->getUsersNotAdmin()[0];

         // Anonyme (Pas d'accès)
        $this->client->request('GET', $this->router->generate('admin_guest_update', ['id' => $guest->getId()]));
        self::assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        self::assertRouteSame('admin_login');

        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $adminUser = $userRepository->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com']);

        $this->client->loginUser($adminUser);
        $this->client->request('GET', $this->router->generate('admin_guest_update', ['id' => $guest->getId()]));

        $this->client->submitForm('Mettre à jour', [
            'guest[name]' => 'Nouvel utilisateur',
            'guest[username]' => 'Nouvel_utilisateur',
            'guest[email]' => $guest->getEmail(),
            'guest[description]' => 'Description du nouvel utilisateur',
        ]);
        self::assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        self::assertRouteSame('admin_guest_index');
    }

    public function testUserCanDeleteGuest(): void
    {
        $guest = new User();
        $guest->setName('Nouvel utilisateur_to_delete')
            ->setUsername('Nouvel_utilisateur_to_delete')
            ->setEmail('newguest@localhost')
            ->setPassword('fake_hashed_password')
        ->setDescription('Description du nouvel utilisateur');
        $manager = $this->client->getContainer()->get('doctrine')->getManager();
        $manager->persist($guest);
        $manager->flush();

        // Anonyme (Pas de suppression)
        $this->client->request('GET', $this->router->generate('admin_guest_delete', ['id' => $guest->getId()]));
        self::assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        self::assertRouteSame('admin_login');

        // Guest (Pas de suppression)
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $guestUser = $this->client->getContainer()->get(UserRepository::class)->getUsersNotAdmin()[0];
        $this->client->loginUser($guestUser);

        $this->client->request('GET', $this->router->generate('admin_guest_delete', ['id' => $guest->getId()]));
        self::assertResponseStatusCodeSame(403);

        // Administrateur
        $adminUser = $userRepository->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com']);
        $this->client->loginUser($adminUser);
        $this->client->request('GET', $this->router->generate('admin_guest_delete', ['id' => $guest->getId()]));
        self::assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        self::assertRouteSame('admin_guest_index');
    }

    public function testUserCanToggleGuest(): void
    {
        $guest = new User();
        $guest->setName('Nouvel utilisateur_to_toggle')
            ->setUsername('Nouvel_utilisateur_to_toggle')
            ->setEmail('newguest@localhost')
            ->setPassword('fake_hashed_password')
            ->setActive(true)
            ->setDescription('Description du nouvel utilisateur');
        $manager = $this->client->getContainer()->get('doctrine')->getManager();
        $manager->persist($guest);
        $manager->flush();

        // Anonyme
        $this->client->request('GET', $this->router->generate('admin_guest_toggle', ['id' => $guest->getId()]));
        self::assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        self::assertRouteSame('admin_login');

        // Guest
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $guestUser = $this->client->getContainer()->get(UserRepository::class)->getUsersNotAdmin()[0];
        $this->client->loginUser($guestUser);

        $this->client->request('GET', $this->router->generate('admin_guest_toggle', ['id' => $guest->getId()]));
        self::assertResponseStatusCodeSame(403);

        // Administrateur
        $adminUser = $userRepository->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com']);
        $this->client->loginUser($adminUser);
        $this->client->request('GET', $this->router->generate('admin_guest_toggle', ['id' => $guest->getId()]));
        self::assertResponseStatusCodeSame(302);
        $this->client->followRedirect();
        self::assertRouteSame('admin_guest_index');

        $guestId = $guest->getId();
        $guest = $this->client->getContainer()->get(UserRepository::class)->find($guestId);
        self::assertSame(false, $guest->isActive());

        $this->client->request('GET', $this->router->generate('admin_guest_toggle', ['id' => $guestId]));
        $guest = $this->client->getContainer()->get(UserRepository::class)->find($guestId);
        self::assertSame(true, $guest->isActive());

    }

    public function testAddGuestThrowsException(): void
    {
        $this->client->disableReboot();

        $mockService = $this->createMock(GuestService::class);
        $mockService->method('addUser')->willThrowException(new \Exception('Erreur ajout'));
        $this->client->getContainer()->set(GuestService::class, $mockService);

        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $adminUser = $userRepository->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com']);
        $this->client->loginUser($adminUser);

        $this->client->request('GET', $this->router->generate('admin_guest_add'));
        $this->client->submitForm('Ajouter', [
            'guest[name]' => 'Exception utilisateur',
            'guest[username]' => 'Exception_utilisateur',
            'guest[email]' => 'newguest@localhost',
            'guest[description]' => 'Description exception',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.alert-danger');
    }

    public function testEditGuestThrowsException(): void
    {
        $this->client->disableReboot();

        $mockService = $this->createMock(GuestService::class);
        $mockService->method('editUser')->willThrowException(new \Exception('Erreur modification'));
        $this->client->getContainer()->set(GuestService::class, $mockService);

        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $adminUser = $userRepository->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com']);
        $guest = $userRepository->findOneBy(['email' => 'guest@localhost']);

        $this->client->loginUser($adminUser);
        $this->client->request('GET', $this->router->generate('admin_guest_update', ['id' => $guest->getId()]));
        $this->client->submitForm('Mettre à jour', [
            'guest[name]' => 'Modifié',
            'guest[username]' => 'Modifié',
            'guest[email]' => 'guest@localhost',
            'guest[description]' => 'Description modifiée',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.alert-danger');
    }

    public function testDeleteGuestIsRemovingImages(): void
    {
        $manager = $this->client->getContainer()->get('doctrine')->getManager();

        $guest = new User();
        $guest->setName('Guest_to_delete_with_medias')
            ->setUsername('Guest_to_delete_with_medias')
            ->setEmail('newguest@localhost')
            ->setPassword('fake_hashed_password')
            ->setActive(true)
            ->setDescription('Guest qui va être supprimé');
        $manager->persist($guest);

        $media = new Media();
        $media->setTitle('Photo du guest à supprimer')
            ->setPath('test-delete.jpg')
            ->setUser($guest);
        $manager->persist($media);
        $manager->flush();

        $mediaTitle = $media->getTitle();

        $this->client->request('GET', $this->router->generate('portfolio'));
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', $mediaTitle);

        // Suppression du Guest
        $adminUser = $this->client->getContainer()
            ->get(UserRepository::class)
            ->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com']);
        $this->client->loginUser($adminUser);
        $this->client->request('GET', $this->router->generate('admin_guest_delete', ['id' => $guest->getId()]));

        $this->client->request('GET', $this->router->generate('portfolio'));
        self::assertResponseIsSuccessful();
        self::assertSelectorTextNotContains('body', $mediaTitle);
    }

    public function testDisableGuestDisableImagesFromFront(): void
    {
        $manager = $this->client->getContainer()->get('doctrine')->getManager();

        $guest = new User();
        $guest->setName('Guest_to_disable_with_medias')
            ->setUsername('Guest_to_disable_with_medias')
            ->setEmail('newguest@localhost')
            ->setPassword('fake_hashed_password')
            ->setActive(true)
            ->setDescription('Guest qui va être désactivé');
        $manager->persist($guest);

        $media = new Media();
        $media->setTitle('Photo du guest à désactiver')
            ->setPath('test-disable.jpg')
            ->setUser($guest);
        $manager->persist($media);
        $manager->flush();

        $mediaTitle = $media->getTitle();
        $guestId = $guest->getId();

        $this->client->request('GET', $this->router->generate('portfolio'));
        self::assertSelectorTextContains('body', $mediaTitle);

        $adminUser = $this->client->getContainer()
            ->get(UserRepository::class)
            ->findOneBy(['email' => 'ina_zaoui@ina_zaoui.com']);
        $this->client->loginUser($adminUser);
        $this->client->request('GET', $this->router->generate('admin_guest_toggle', ['id' => $guestId]));
        $manager->clear();
        $guestReloaded = $this->client->getContainer()->get(UserRepository::class)->find($guestId);
        self::assertFalse($guestReloaded->isActive());

        $this->client->request('GET', $this->router->generate('portfolio'));
        self::assertResponseIsSuccessful();
        self::assertSelectorTextNotContains('body', $mediaTitle);
    }
}
