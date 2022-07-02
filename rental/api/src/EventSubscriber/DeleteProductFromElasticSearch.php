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

class DeleteProductFromElasticSearch implements EventSubscriberInterface
{

    private ElasticSearchService $elasticSearchService;
    private EmailVerifier $emailVerifier;
    private TokenStorageInterface $tokenStorage;

    public function __construct(ElasticSearchService $elasticSearchService,EmailVerifier $emailVerifier,TokenStorageInterface $tokenStorage)
    {
        $this->elasticSearchService = $elasticSearchService;
        $this->emailVerifier = $emailVerifier;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['deleteProductAndSendEmail', EventPriorities::PRE_WRITE],
        ];
    }



    public function deleteProductAndSendEmail(ViewEvent $event)
    {

        $product = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();


        if ($product instanceof Product
            && Request::METHOD_DELETE === $method
        ) {

            if (in_array('ROLE_ADMIN',$this->getUser()->getRoles())){
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $this->getUser(),
                    (new TemplatedEmail())
                        ->from(new Address('devfullstack44@gmail.com', 'Rentme Mail Bot'))
                        ->to($product->getUser()->getEmail())
                        ->subject('Suppression product')
                        ->htmlTemplate('admin/delete_product.html.twig')
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