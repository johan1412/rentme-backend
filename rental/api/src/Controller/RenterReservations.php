<?php

namespace App\Controller;

use App\Manager\UserReservationsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserReservations extends AbstractController
{
    protected $userReservationsManager;
    public function __construct(UserReservationsManager $userReservationsManager){
        $this->userReservationsManager = $userReservationsManager;
    }


    /**
     * @return ProductsNotValidManager
     */
    public function __invoke()
    {
        return $this->userReservationsManager->getReservations();
    }

}
