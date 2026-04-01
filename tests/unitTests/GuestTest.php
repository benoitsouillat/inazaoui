<?php

declare(strict_types=1);

namespace App\Tests\unitTests;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class GuestTest extends TestCase
{
    private User $guest;

    protected function setUp(): void
    {
        $this->guest = new User();
    }
}
