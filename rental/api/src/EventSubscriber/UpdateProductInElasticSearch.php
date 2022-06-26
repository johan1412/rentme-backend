<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Product;
use App\Entity\Reservation;
use App\Entity\User;
use App\Security\EmailVerifier;
use App\Service\ElasticSearchService;
use Cassandra\Type\UserType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mime\Address;

class UpdateProductInElasticSearch implements EventSubscriberInterface
{

    private ElasticSearchService $elasticSearchService;

    public function __construct(ElasticSearchService $elasticSearchService)
    {
        $this->elasticSearchService = $elasticSearchService;
    }

    public static function getSubscribedEvents(): array
    {

        return [
            KernelEvents::VIEW => ['updateProduct', EventPriorities::POST_WRITE],
        ];
    }

    public function updateProduct(ViewEvent $event)
    {
        $product = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();


        if ($product instanceof Product
            && Request::METHOD_PATCH === $method
        ) {

            $params = [
                'index' => 'product',
                'id'    => $product->getId()
            ];
            $this->elasticSearchService->getElasticClient()->delete($params);

            $params = [
                'index' => 'product',
                'id'    => $product->getId(),
                'body'  => [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'description' => $product->getDescription(),
                    'price' => $product->getPrice(),
                    'average_ratings' => $product->getAverageRatings(),
                    'numbers_of_ratings' => $product->getNumbersOfRatings(),
                    'ville' => $product->getAddress()->getCity(),
                    'region' => $product->getAddress()->getRegion()->getName(),
                    'region_number' => $product->getAddress()->getRegion()->getNumber(),
                    'category' => $product->getCategory()->getName(),
                    'image_path' => $product->getFiles()->first()->getPath(),
                    'is_valid' => $product->getIsValid(),
                    "has_right" => $product->getHasRight()
                ]
            ];
            $this->elasticSearchService->getElasticClient()->index($params);
        }
    }

}