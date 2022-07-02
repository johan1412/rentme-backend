<?php


namespace App\Controller;
use App\Entity\Message;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class MessageController extends AbstractController
{

    private $userRepository;


    public function __construct(UserRepository $userRepository )
    {
        $this->userRepository = $userRepository;
    }

    public function getConversations(ManagerRegistry $doctrine, $id): Response
    {

        $user = $this->userRepository->find($id);
        if(!$user){
            throw $this->createNotFoundException('The user does not exist');
        }else{
            if($user != $this->getUser() ){
                throw  $this->createAccessDeniedException();
            }
        }

        $em = $doctrine->getManager();
        $query = $em->createQuery(
          'SELECT IDENTITY(m.sender) as sender, IDENTITY(m.reciever) as reciever,
            IDENTITY(m.product) as productId, m.text, p.name as productName, u.id as userId, u.firstName as firstname, u.lastName as lastname
          FROM App\Entity\Message m
          JOIN App\Entity\User u WITH (u.id = m.sender OR u.id = m.reciever)
          JOIN App\Entity\Product p WITH (p.id = m.product)
          WHERE (m.reciever = '. $id .
              'OR m.sender ='.$id . 
              ') AND u.id <> '.$id
        );
        $messages = $query->getArrayResult();
        $conversations = array();
        foreach ($messages as $message){
            $conversations[$message['productId']][$message['firstname'] . ' ' . $message['lastname']][] = $message;
        }

        return new Response(json_encode($conversations));
    }
}