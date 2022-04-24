<?php

namespace App\EventListener;

use App\Entity\User;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class JWTCreatedListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;
    private $productRepository;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack,ProductRepository $productRepository)
    {
        $this->requestStack = $requestStack;
        $this->productRepository = $productRepository;
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        if ($user instanceof User) {
            if (!$user->isVerified()){
                return;
            }
            if (in_array("ROLE_ADMIN", $user->getRoles())){
                $data['data'] = array(
                    'id'        => $user->getId(),
                    'email'     => $user->getEmail(),
                    'firstName'     => $user->getFirstName(),
                    'lastName'     => $user->getLastName(),
                    'roles'     => $user->getRoles(),
                    'numberOfProductsNotValid' => $this->getNumberOfProductsNotValid(),
                );
            }else{
                $data['data'] = array(
                    'id'        => $user->getId(),
                    'email'     => $user->getEmail(),
                    'firstName'     => $user->getFirstName(),
                    'lastName'     => $user->getLastName(),
                    'roles'     => $user->getRoles()
                );
            }
        }

        $event->setData($data);
    }


    public function getProductsNotValid(){
        $products = $this->productRepository->findBy(["isValid" => false]);
        return $products;
    }

    public function getNumberOfProductsNotValid(){
        $products = $this->getProductsNotValid();
        return count($products);
    }

}