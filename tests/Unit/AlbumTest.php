<?php

namespace App\Tests\Unit;

use App\Entity\Album;
use PHPUnit\Framework\TestCase;

class AlbumTest extends TestCase
{
    private Album $album;

    public function setUp(): void
    {
        $this->album = new Album();
        $this->album->setName('Album 1');
    }

    public function testIsTrue()
    {
        self::assertSame('Album 1', $this->album->getName());
    }

    public function testIsFalse()
    {
        self::assertNotSame('Album 2', $this->album->getName());
    }
}
