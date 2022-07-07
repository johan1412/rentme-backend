<?php

namespace App\Controller;

use App\Manager\RenterReservationsManager;
use App\Manager\UserReservationsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RenterReservations extends AbstractController
{
    protected $renterReservationsManager;
    public function __construct(RenterReservationsManager $renterReservationsManager){
        $this->renterReservationsManager = $renterReservationsManager;
    }


    /**
     * @return ProductsNotValidManager
     */
    public function __invoke()
    {
        return $this->renterReservationsManager->getReservations();
    }

}
