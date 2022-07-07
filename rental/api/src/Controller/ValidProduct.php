<?php

namespace App\Controller;

use App\Manager\ValidProductManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ValidProduct extends AbstractController
{
    protected $validProductManager;
    public function __construct(ValidProductManager $validProductManager){
        $this->validProductManager = $validProductManager;
    }

    /**
     * @return ValidProductManager
     */
    public function __invoke($data)
    {
        return $this->validProductManager->getProductValid($data);
    }

}
