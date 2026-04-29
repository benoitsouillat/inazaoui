<?php

namespace App\Tests\Unit\Event;

use App\Entity\User;
use App\Event\GuestEvent;
use PHPUnit\Framework\TestCase;

class GuestEventTest extends TestCase
{
    public function testIfUserIsSet(): void
    {
        $user = new User();
        $event = new GuestEvent($user);
        $this->assertSame($user, $event->getGuest());
    }

}
