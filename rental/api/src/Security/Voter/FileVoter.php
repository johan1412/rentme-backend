<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class FileVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ['FILE_CREATE', 'FILE_EDIT', 'FILE_DELETE'])
            && $subject instanceof \App\Entity\File;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'FILE_CREATE':
                if ($subject->getProduct()->getUser() == $user) {
                    return true;
                }
                break;
            case 'FILE_EDIT':
                if ($subject->getProduct()->getUser() == $user) {
                    return true;
                }
                break;
            case 'FILE_DELETE':
                if ( $this->security->isGranted(Role::ADMIN) ) {
                    return true;
                }
                if ($subject->getProduct()->getUser() == $user) {
                    return true;
                }
                break;
        }

        return false;
    }
}
