<?php


namespace App\Manager;

use App\Entity\User;
use App\Repository\ProductRepository;
use App\Service\ElasticSearchService;
use Elastic\Elasticsearch\ClientBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class ValidProductManager
{
    protected $productRepository;
    protected $tokenStorage;

    public function __construct(ProductRepository $productRepository,TokenStorageInterface $tokenStorage)
    {
        $this->productRepository = $productRepository;
        $this->tokenStorage = $tokenStorage;
    }



    public function getProductValid($data){

        if($this->getUser() === null){
            if (!$data->getIsValid() || !$data->getHasRight()){
                return new JsonResponse(['message'=>'Access denied'],403);
            }
        }else{
            if (!in_array('ROLE_ADMIN',$this->getUser()->getRoles()) && $this->getUser() !== $data->getUser()){
                if (!$data->getIsValid() || !$data->getHasRight()){
                    return new JsonResponse(['message'=>'Access denied'],403);
                }
            }
        }
        return $data;
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
