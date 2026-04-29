<?php

namespace App\Tests\Unit;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class MediaTest extends TestCase
{
    private Media $media;
    private Album $album;
    private User $user;

    public function setUp(): void
    {
        $this->album = new Album();
        $this->album->setName('Album 1');

        $this->user = new User;
        $this->user->setName('user1');

        $this->media = new Media();
        $this->media->setTitle('Media Title')
                ->setPath('/path/to/media.jpg')
                ->setAlbum($this->album)
                ->setUser($this->user);
    }

    public function testIsTrue()
    {
        self::assertSame('Media Title', $this->media->getTitle());
        self::assertSame('/path/to/media.jpg', $this->media->getPath());
        self::assertSame('Album 1', $this->media->getAlbum()->getName());
        self::assertSame('user1', $this->media->getUser()->getName());
    }

    public function testIsFalse()
    {
        self::assertNotSame('False Title', $this->media->getTitle());
        self::assertNotSame('/path/to/false/media.jpg', $this->media->getPath());
        self::assertNotSame('Album 2', $this->media->getAlbum()->getName());
        self::assertNotSame('user2', $this->media->getUser()->getName());
    }

    public function testIfMediaIsSetToAlbum()
    {
        $album2 = new Album();
        $this->media->setAlbum($album2);
        self::assertSame($album2, $this->media->getAlbum());
    }

    public function testIfMediaSetUser()
    {
        $user2 = new User();
        $this->media->setUser($user2);
        self::assertSame($user2, $this->media->getUser());
    }
    public function testIfFileIsSetToMedia()
    {
        $file = $this->createMock('Symfony\Component\HttpFoundation\File\File');
        $this->media->setFile($file);
        self::assertSame($file, $this->media->getFile());
    }
}
