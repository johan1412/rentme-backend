<?php

namespace App\Controller;

use App\Manager\ProductsNotValidManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductsNotValid extends AbstractController
{
    protected $productsNotValidManager;
    public function __construct(ProductsNotValidManager $productsNotValidManager){
        $this->productsNotValidManager = $productsNotValidManager;
    }

    /**
     * @return ProductsNotValidManager
     */
    public function __invoke()
    {
        return $this->productsNotValidManager->getNumberOfProductsNotValid();
    }

}
