<?php


namespace App\Manager;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\JsonResponse;


class ProductsValidManager
{
    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getProductsValid(){
        $products = $this->productRepository->findBy(["isValid" => true]);
        return $products;
    }

}
