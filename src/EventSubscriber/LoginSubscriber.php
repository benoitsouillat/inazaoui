<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSubscriber
{
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }
        if (!$user->isActive()) {
            $event->getRequest()->getSession()->getFlashBag()->add('error', 'Votre compte est désactivé.');
            $event->getRequest()->getSession()->invalidate();
        }
    }
}
