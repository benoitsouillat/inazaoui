<?php

namespace App\Tests\unitTests;

use App\Entity\Album;
use PHPUnit\Framework\TestCase;

class AlbumTest extends TestCase
{
    private Album $album;

    public function setUp(): void
    {
        $this->album = new Album();
    }

    public function testIsTrue()
    {
        $this->album->setName('Album 1');
        self::assertSame('Album 1', $this->album->getName());
    }

    public function testIsFalse()
    {
        $this->album->setName('Album 2');
        self::assertNotSame('Album 1', $this->album->getName());
    }
}
