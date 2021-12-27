<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ReservationVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ['RESERVATION_EDIT', 'RESERVATION_DELETE'])
            && $subject instanceof \App\Entity\Reservation;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'RESERVATION_EDIT':
                if ($subject->getUser() == $user) {
                    return true;
                }
                break;
            case 'RESERVATION_DELETE':
                if ( $this->security->isGranted(Role::ADMIN) ) {
                    return true;
                }
                if ($subject->getUser() == $user) {
                    return true;
                }
                break;
        }

        return false;
    }
}