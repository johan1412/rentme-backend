<?php


namespace App\Service;

use App\Repository\ProductRepository;
use Elasticsearch\ClientBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;


class ElasticSearchService
{
    private $client;

    public function __construct()
    {
        $this->client = \Elastic\Elasticsearch\ClientBuilder::create()
            ->setElasticCloudId(getenv('ELASTIC_CLOUD_ID'))
            ->setApiKey(getenv('ELASTIC_API_KEY'))
            ->build();
    }

    public function getElasticClient(){
        return $this->client;
    }

}
