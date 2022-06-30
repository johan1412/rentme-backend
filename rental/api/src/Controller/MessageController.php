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
    public function getConversations(ManagerRegistry $doctrine, $id): Response
    {

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
            if($message['sender'] != $id) {
              $conversations[$message['productId']][$message['firstname'] . ' ' . $message['lastname']][] = $message;
            } else {
              $conversations[$message['productId']][$message['firstname'] . ' ' . $message['lastname']][] = $message;
            }
        }

        return new Response(json_encode($conversations));
    }
}