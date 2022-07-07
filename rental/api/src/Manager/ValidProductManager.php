<?php


namespace App\Manager;

use App\Repository\ProductRepository;
use App\Service\ElasticSearchService;
use Elastic\Elasticsearch\ClientBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;


class ValidProductManager
{
    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }



    public function getProductValid($data){

        $data;
        if (!$data->getIsValid() || !$data->getHasRight()){
           return new JsonResponse(['message'=>'Access denied'],403);
        }
        return $data;
    }

}
