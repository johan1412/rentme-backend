<?php


namespace App\Controller;
use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Repository\ProductRepository;
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
    private $messageRepository;
    private $productRepository;


    public function __construct(UserRepository $userRepository, MessageRepository $messageRepository, ProductRepository $productRepository)
    {
        $this->userRepository = $userRepository;
        $this->messageRepository = $messageRepository;
        $this->productRepository = $productRepository;
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
          'SELECT IDENTITY(m.sender) as sender, IDENTITY(m.reciever) as reciever, m.isRead, 
            IDENTITY(m.product) as productId, m.text, p.name as productName, u.id as userId, u.firstName as firstname, u.lastName as lastname
          FROM App\Entity\Message m
          JOIN App\Entity\User u WITH (u.id = m.sender OR u.id = m.reciever)
          LEFT JOIN App\Entity\Product p WITH (p.id = m.product)
          WHERE (m.reciever = '. $id .
              'OR m.sender ='.$id . 
              ') AND u.id <> '.$id.
          "ORDER BY m.id DESC"
        );
        $messages = $query->getArrayResult();
        $conversations = array();

        foreach ($messages as $message){
            if($message['productId']==null){
                $message['productId'] = -1;
                $message['productName'] = "Pas de produit";
            }
            $conversations[$message['productId']][$message['firstname'] . ' ' . $message['lastname']][] = $message;
        }
        foreach ($conversations as $key1 => $conversationProduct){
            foreach($conversationProduct as $key2 => $conversation){
                $conversations[$key1][$key2] = array_reverse($conversations[$key1][$key2]);
            }
        }

        return new Response(json_encode($conversations));
    }

    public function getNbUnreadMessages(ManagerRegistry $doctrine, $id): Response
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
            'SELECT COUNT(m) 
          FROM App\Entity\Message m
          WHERE
           m.isRead = false AND 
           m.reciever = '. $id
        );
        $nb = $query->getResult();
        $nb = ["nb" => $nb[0]["1"]];

        return new Response(json_encode($nb));
    }

    public function setReadMessages(ManagerRegistry $doctrine, $senderId, $recieverId, $productId): Response
    {
        $reciever = $this->userRepository->find($recieverId);
        $sender = $this->userRepository->find($senderId);

        if(!$reciever || !$sender){
            throw $this->createNotFoundException('The user does not exist');
        }else{
            if($reciever != $this->getUser() ){
                throw  $this->createAccessDeniedException();
            }
        }

        if($productId != 'none')
            $product = $this->productRepository->find($productId);
        else
            $product = null;

        $messages = $this->messageRepository->findBy([
            'reciever' => $reciever,
            'sender' => $sender,
            'isRead' => false,
            'product' => $product
        ]);


        $em = $doctrine->getManager();

        foreach ($messages as $message){
            $message->setIsRead(true);
            $em->persist($message);
        }
        $em->flush();

        return new Response(json_encode($messages));
    }


}