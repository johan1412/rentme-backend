<?php


namespace App\Controller;
use App\Entity\Message;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends AbstractController
{
    public function getConversations(ManagerRegistry $doctrine, $id, Request $req){

        $repository = $doctrine->getRepository(Message::class);
        $em = $doctrine->getManager();
        $query = $em->createQuery(
            'SELECT m
        FROM App\Entity\Message m
        WHERE m.recieverId = '. $id .
            'OR m.senderId ='.$id
        );
        $messages = $query->getArrayResult();
        $conversations = array();
        foreach ($messages as $message){
            if($message['senderId'] != $id)
                $conversations[$message['productId']][$message['senderId']][] = $message;
            else
                $conversations[$message['productId']][$message['recieverId']][] = $message;
        }

        return new Response(json_encode($conversations));
    }
}