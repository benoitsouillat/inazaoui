<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MediaVoter extends Voter
{
    public const string VIEW = 'MEDIA_VIEW';
    public const string EDIT = 'MEDIA_EDIT';
    public const string DELETE = 'MEDIA_DELETE';

    public function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE]);
    }

    public function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        /** Si l'utilisateur n'est pas connecté, il n'a pas d'accès */
        if (!$user instanceof User)
        {
            return false;
        }

        /** Si l'utilisateur est admin, il peut tout faire */
        if (in_array('ROLE_ADMIN', $user->getRoles()))
        {
            return true;
        }

        /** Si l'utilisateur n'est pas admin, il peut uniquement agir sur ses médias */
        switch ($attribute) {
            case self::VIEW:
            case self::EDIT:
            case self::DELETE:
                return $subject->getUser() === $user;
        }

        return false;
    }

}
