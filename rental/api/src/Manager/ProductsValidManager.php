<?php


namespace App\Manager;

use App\Repository\ProductRepository;
use App\Service\ElasticSearchService;
use Elastic\Elasticsearch\ClientBuilder;


class ProductsValidManager
{
    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }



    public function getProductsValid(){
        $products = $this->productRepository->findBy(["isValid" => true,"hasRight" => true]);
        return $products;
    }

}
