<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Media;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = (new User())
            ->setName('John')
            ->setUsername('johndoe')
            ->setEmail('john@doe.fr')
            ->setDescription('Lorem Ipsum')
            ->setActive(true)
            ->setPassword('hashed_password');
    }

    public function testUserAlwaysHasRoleUser(): void
    {
        $this->assertContains('ROLE_USER', $this->user->getRoles());
    }

    public function testSetRolesAddsCustomRole(): void
    {
        $this->user->setRoles(['ROLE_ADMIN']);

        $this->assertContains('ROLE_ADMIN', $this->user->getRoles());
        $this->assertContains('ROLE_USER', $this->user->getRoles());
    }

    public function testRolesAreUnique(): void
    {
        $this->user->setRoles(['ROLE_USER', 'ROLE_USER']);

        $roles = $this->user->getRoles();
        $this->assertSame(array_unique($roles), $roles);
    }

    public function testGetNameReturnsSetValue(): void
    {
        $this->assertSame('John', $this->user->getName());
    }

    public function testGetUsernameReturnsSetValue(): void
    {
        $this->assertSame('johndoe', $this->user->getUsername());
    }

    public function testGetEmailReturnsSetValue(): void
    {
        $this->assertSame('john@doe.fr', $this->user->getEmail());
    }

    public function testGetDescriptionReturnsSetValue(): void
    {
        $this->assertSame('Lorem Ipsum', $this->user->getDescription());
    }

    public function testDescriptionCanBeNull(): void
    {
        $this->user->setDescription(null);

        $this->assertNull($this->user->getDescription());
    }

    public function testGetPasswordReturnsSetValue(): void
    {
        $this->assertSame('hashed_password', $this->user->getPassword());
    }

    public function testGetUserIdentifierReturnsUsername(): void
    {
        $this->assertSame('johndoe', $this->user->getUserIdentifier());
    }

    public function testIdIsNullBeforePersist(): void
    {
        $this->assertNull($this->user->getId());
    }

    public function testUserIsActiveByDefault(): void
    {
        $freshUser = new User();

        $this->assertTrue($freshUser->isActive());
    }

    public function testUserCanBeDeactivated(): void
    {
        $this->user->setActive(false);

        $this->assertFalse($this->user->isActive());
    }

    public function testSetMediasAssignsCollection(): void
    {
        $medias = new ArrayCollection([new Media(), new Media()]);
        $this->user->setMedias($medias);

        $this->assertSame($medias, $this->user->getMedias());
    }

    public function testSetMediasReplacesExistingCollection(): void
    {
        $first = new ArrayCollection([new Media()]);
        $second = new ArrayCollection([new Media(), new Media()]);

        $this->user->setMedias($first);
        $this->assertNotCount(2, $this->user->getMedias());

        $this->user->setMedias($second);
        $this->assertCount(2, $this->user->getMedias());
    }

    public function testSerializeHashesPassword(): void
    {
        $serializedData = $this->user->__serialize();
        $expectedHash = hash('crc32c', 'hashed_password');

        $passwordKey = "\0" . User::class . "\0password";

        $this->assertArrayHasKey($passwordKey, $serializedData);
        $this->assertSame($expectedHash, $serializedData[$passwordKey]);
    }
}
