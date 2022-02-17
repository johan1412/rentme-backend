<?php


namespace App\Manager;

use App\Entity\User;
use App\Repository\ProductRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class UserReservationsManager extends AbstractController
{
    protected $reservationRepository;
    protected $tokenStorage;

    public function __construct(ReservationRepository $reservationRepository,TokenStorageInterface $tokenStorage)
    {
        $this->reservationRepository = $reservationRepository;
        $this->tokenStorage = $tokenStorage;
    }


    public function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }

    public function getReservations(){
        $reservations = $this->reservationRepository->findBy(["user" => $this->getUser()]);
        return $reservations;
    }

}
