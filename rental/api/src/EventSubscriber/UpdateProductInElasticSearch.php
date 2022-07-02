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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UpdateProductInElasticSearch implements EventSubscriberInterface
{

    private ElasticSearchService $elasticSearchService;
    private TokenStorageInterface $tokenStorage;
    private EmailVerifier $emailVerifier;

    public function __construct(ElasticSearchService $elasticSearchService,TokenStorageInterface $tokenStorage,EmailVerifier $emailVerifier)
    {
        $this->elasticSearchService = $elasticSearchService;
        $this->tokenStorage = $tokenStorage;
        $this->emailVerifier = $emailVerifier;
    }

    public static function getSubscribedEvents(): array
    {

        return [
            KernelEvents::VIEW => ['updateProductAndSendEmail', EventPriorities::PRE_WRITE],
        ];
    }

    public function updateProductAndSendEmail(ViewEvent $event)
    {


        $product = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();


        if ($product instanceof Product
            && Request::METHOD_PATCH === $method
        ) {
            if (in_array('ROLE_ADMIN',$this->getUser()->getRoles()) && $product->getIsValid() == true){
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $this->getUser(),
                    (new TemplatedEmail())
                        ->from(new Address('devfullstack44@gmail.com', 'Rentme Mail Bot'))
                        ->to($product->getUser()->getEmail())
                        ->subject('Validation product')
                        ->htmlTemplate('admin/valid_product.html.twig')
                        ->context([
                            'product' => $product->getName(),
                            'category' => $product->getCategory()->getName(),
                        ])
                );
            }

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

    public function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }

}