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

class DeleteProductFromElasticSearch implements EventSubscriberInterface
{

    private ElasticSearchService $elasticSearchService;

    public function __construct(ElasticSearchService $elasticSearchService)
    {
        $this->elasticSearchService = $elasticSearchService;
    }

    public static function getSubscribedEvents(): array
    {

        return [
            KernelEvents::VIEW => ['indexProduct', EventPriorities::PRE_WRITE],
        ];
    }

    public function indexProduct(ViewEvent $event)
    {
        $product = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();


        if ($product instanceof Product
            && Request::METHOD_DELETE === $method
        ) {
            $params = [
                'index' => 'product',
                'id'    => $product->getId()
            ];
            return $this->elasticSearchService->getElasticClient()->delete($params);
        }
    }

}