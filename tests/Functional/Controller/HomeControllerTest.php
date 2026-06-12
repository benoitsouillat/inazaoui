<?php

namespace App\Tests\Functional\Controller;

use App\Repository\AlbumRepository;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    private $client;
    private $router;

    public function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->router = $this->client->getContainer()->get('router');
    }

    public function testHome(): void
    {
        $this->client->request('GET', $this->router->generate('home'));
        self::assertResponseIsSuccessful();
    }

    public function testPortfolioShowsOnlyActiveMedias(): void
    {
        $this->client->request('GET', $this->router->generate('portfolio'));
        self::assertResponseIsSuccessful();

        $activeMedias = $this->client->getContainer()->get(MediaRepository::class)->findAllActiveByPage(1);
        $images = $this->client->getCrawler()->filter('div.media');

        $this->assertCount(count($activeMedias), $images);
    }

    public function testPortfolioShowsOnlyActiveMediasOfAlbum(): void
    {
        $album = $this->client->getContainer()->get(AlbumRepository::class)->findOneBy([]);

        $this->client->request('GET', $this->router->generate('portfolio', ['id' => $album->getId()]));
        self::assertResponseIsSuccessful();

        $activeMedias = $this->client->getContainer()->get(MediaRepository::class)->findActiveByAlbum($album);
        $images = $this->client->getCrawler()->filter('div.media');

        $this->assertCount(count($activeMedias), $images);

    }

    public function testGuestsShowsOnlyGuestWithOneOrMoreMedia(): void
    {
        $this->client->request('GET', $this->router->generate('guests'));
        self::assertResponseIsSuccessful();
        $activeGuests = $this->client->getContainer()->get(UserRepository::class)->getActiveGuestsNotAdmin();
        $cards = $this->client->getCrawler()->filter('div.guest');

        $this->assertCount(count($activeGuests), $cards);
    }

    public function testAboutFrontPage(): void
    {
        $this->client->request('GET', $this->router->generate('about'));
        self::assertResponseIsSuccessful();
    }

}
