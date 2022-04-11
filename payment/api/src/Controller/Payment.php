<?php

namespace App\Controller;

use App\Entity\Pack;
use App\Entity\Transaction;
use GuzzleHttp\Exception\BadResponseException;
use http\Client;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

Class Payment extends AbstractController{

    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @Route("/success/{productId}/{price}/{renterId}/{tenantId}", name="success",methods={"GET"})
     */
    public function success(Request $request,$productId,$price,$renterId,$tenantId): Response
    {
        if(!$productId && !$price && !$renterId && !$tenantId){
            return new BadResponseException(['message'=> "Wrong data, product doesn't exist"]);
        }

        $em = $this->getDoctrine()->getManager();
        $transaction = new Transaction();
        $transaction->setAmount($price);
        $transaction->setProductId($productId);
        $transaction->setRenterId($renterId);
        $transaction->setTenantId($tenantId);
        $transaction->setDate(new \DateTime());
        $em->persist($transaction);
        $em->flush();

        $url = 'http://localhost:8080/success';
        return $this->redirect($url);
    }

    public function getToken(){

        $response = $this->client->request(
            'POST',
            'https://localhost:8443/authentication_token',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => [
                    'email' => 'admin@gmail.com',
                    'password' => 'mdpmdpmdp'
                ]
            ]

        );
        return $response->toArray()['token'];
    }

    public function fetchInformationFromRentMe($objectName,$id){
        $response = $this->client->request(
            'GET',
            'https://localhost:8443/'.$objectName.'/'.$id,
            [
                'headers' => [
                'Authorization' => 'Bearer '.$this->getToken(),
                ]
            ]
        );
        if ($response->getStatusCode() === 200){
            return $response->toArray();
        }
        return [];
    }

    /**
     * @Route("/error", name="error",methods={"GET"})
     */
    public function error(): Response
    {
        return new JsonResponse(["Failed payment"]);
    }

    /**
     * @Route("/create-checkout-session", name="checkout",methods={"POST"})
     */
    public function checkout(Request $request): Response
    {
        return $this->getToken();
        $parameters = json_decode($request->getContent(), true);
        if(!$parameters["tenant"]){
            return new BadResponseException(['message'=> "Wrong data, tenant doesn't exist"]);
        }
        if(!$parameters["product"]){
            return new BadResponseException(['message'=> "Wrong data, product doesn't exist"]);
        }

        // This is your test secret API key.
        \Stripe\Stripe::setApiKey('sk_test_51ImJIiH1ST2SneRlI5texYpM1EjkRwX5h0sXH8lWH6BxPP2sFmNCXW3KqXvOCnVFnaKxOeSZd9ZhGqaYm2D1mVyl00xvAeAezq');

        $checkout_session = \Stripe\Checkout\Session::create([
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $parameters["product"]["name"],
                        ],
                        'unit_amount' => $parameters["product"]["price"]*100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $this->generateUrl('success',["productId"=>$parameters["product"]["id"],"price"=>$parameters["product"]["price"],"renterId"=>$parameters["product"]["user"]["id"],"tenantId"=>$parameters["tenant"]["id"]],UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('error',[],UrlGeneratorInterface::ABSOLUTE_URL),
            ]);
        return new JsonResponse(['checkout_session'=>$checkout_session]);
    }


    /**
     * @Route("/refund", name="refund",methods={"POST"})
     */
    public function refund(Request $request): Response
    {
        $parameters = json_decode($request->getContent(), true);
        if(!$parameters["caution"]){
            return new BadResponseException(['message'=> "Wrong data, caution doesn't exist"]);
        }
        if(!$parameters["paymentIntent"]){
            return new BadResponseException(['message'=> "Wrong data, paymentIntent doesn't exist"]);
        }

        $stripe = new \Stripe\StripeClient(
            'sk_test_51ImJIiH1ST2SneRlI5texYpM1EjkRwX5h0sXH8lWH6BxPP2sFmNCXW3KqXvOCnVFnaKxOeSZd9ZhGqaYm2D1mVyl00xvAeAezq'
        );
        $stripe->refunds->create([
            'payment_intent' => $parameters["paymentIntent"],
            'amount' => $parameters["caution"]*100
        ]);

        $url = 'http://localhost:8080/refund';
        return $this->redirect($url);
    }
    // pi_3KdFfZH1ST2SneRl1CyAwTX3

    /**
     * @Route("/transfer", name="transfer",methods={"POST"})
     */
    public function transfer(Request $request): Response
    {
        $stripe = new \Stripe\StripeClient(
            'sk_test_51ImJIiH1ST2SneRlI5texYpM1EjkRwX5h0sXH8lWH6BxPP2sFmNCXW3KqXvOCnVFnaKxOeSZd9ZhGqaYm2D1mVyl00xvAeAezq'
        );

        $account = $stripe->accounts->create([
            'type' => 'custom',
            'country' => 'US',
            'email' => 'abdellatifchalal06@gmail.com',
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
        ]);
        dd($account);

        $payout = \Stripe\Payout::create([
            'amount' => 1000,
            'currency' => 'usd',
            'method' => 'instant',
        ], [
            'stripe_account' => $account.id,
        ]);

       // $url = 'http://localhost:8080/success';
        return new JsonResponse(['payout'=>    $payout]);
    }
    // pi_3KdFfZH1ST2SneRl1CyAwTX3
}
