<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class PinVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return $attribute === 'PIN_CREATE' || (in_array($attribute, ['PIN_MANAGE'])
            && $subject instanceof \App\Entity\Pin);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
                // case 'PIN_EDIT':
                //     return $user->isVerified() && $subject->getUser() == $user;
                // case 'PIN_DELETE':
                //     return $user->isVerified() && $subject->getUser() == $user;
            case 'PIN_CREATE':
                return $user->isVerified();
            case 'PIN_MANAGE':
                // logic to determine if the user can EDIT
                // return true or false
                return $user->isVerified() && $subject->getUser() == $user;
        }

        return false;
    }
}
