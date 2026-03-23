<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

final class GuestEvent extends Event
{
    public const GUEST_CREATED = 'guest.created';
    public const GUEST_EDITED = 'guest.edited';
    public const GUEST_DELETED = 'guest.deleted';

    public function __construct(
        private readonly User $guest
    )
    {}

    public function getGuest(): User
    {
        return $this->guest;
    }
}
