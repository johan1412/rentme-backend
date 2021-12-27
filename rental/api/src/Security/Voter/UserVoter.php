<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ['USER_EDIT', 'USER_DELETE'])
            && $subject instanceof \App\Entity\User;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'USER_EDIT':
                if ($subject == $user) {
                    return true;
                }
                break;
            case 'USER_DELETE':
                if ( $this->security->isGranted(Role::ADMIN) ) {
                    return true;
                }
                if ($subject == $user) {
                    return true;
                }
                break;
        }

        return false;
    }
}