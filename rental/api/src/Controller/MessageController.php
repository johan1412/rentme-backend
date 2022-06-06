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
    public function getConversations(ManagerRegistry $doctrine, $id, Request $req): Response
    {

        $repository = $doctrine->getRepository(Message::class);
        $em = $doctrine->getManager();
        $query = $em->createQuery(
            'SELECT IDENTITY(m.sender) as sender, IDENTITY(m.reciever) as reciever, m.text
        FROM App\Entity\Message m 
        JOIN App\Entity\User u WITH (u.id = m.sender OR u.id = m.reciever)
        WHERE m.reciever = '. $id .
            'OR m.sender ='.$id
        );
        $messages = $query->getArrayResult();
        $conversations = array();
        foreach ($messages as $message){
            $message['productId'] = 1;
            if($message['sender'] != $id)
                $conversations[$message['productId']][$message['sender']][] = $message;
            else
                $conversations[$message['productId']][$message['reciever']][] = $message;
        }

        //TODO : REMOVE THIS TEST
        foreach ($messages as $message){
            $message['productId'] = 2;
            if($message['sender'] != $id)
                $conversations[$message['productId']][$message['sender']][] = $message;
            else
                $conversations[$message['productId']][$message['reciever']][] = $message;
        }

        return new Response(json_encode($conversations));
    }
}