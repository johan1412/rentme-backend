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
            ->setElasticCloudId('rentme:dXMtY2VudHJhbDEuZ2NwLmNsb3VkLmVzLmlvOjQ0MyQyYjRlZWIxYWZkYmQ0ZDk3OTE5MWFhNGUyZDQxNWJhOSQ2NjAzNGI4YTI0OTc0MDg0Yjc3YmQ5YTdmOTIwZDgwZg==')
            ->setApiKey('aXl4SG40RUJlQl9KcmR0Slp4N0s6dk8zYTJaN0FRaUd2d2ZIV3FqWGxZUQ==')
            ->build();
    }

    public function getElasticClient(){
        return $this->client;
    }

}
