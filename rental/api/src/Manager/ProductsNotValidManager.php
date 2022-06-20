<?php


namespace App\Manager;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\JsonResponse;


class ProductsNotValidManager
{
    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getProductsNotValid(){
        $products = $this->productRepository->findBy(["isValid" => false,"hasRight" => true]);
        return $products;
    }

    public function getNumberOfProductsNotValid(){
        $products = $this->getProductsNotValid();
        return $products;
    }
}
