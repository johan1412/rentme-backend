<?php

namespace App\Controller;

use App\Manager\ProductsValidManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductsValid extends AbstractController
{
    protected $productsValidManager;
    public function __construct(ProductsValidManager $productsValidManager){
        $this->productsValidManager = $productsValidManager;
    }

    /**
     * @return ProductsValidManager
     */
    public function __invoke()
    {
        return $this->productsValidManager->getProductsValid();
    }

}
