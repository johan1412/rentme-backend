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
                        'currency' => 'eur',
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


    /**
     * @Route("/stripe/account", name="stripe_account",methods={"POST"})
     */
    public function createCustomAccountStripe(Request $request): Response
    {
        /*$parameters = json_decode($request->getContent(), true);
        if(!$parameters["email"]){
            return new BadResponseException(['message'=> "Wrong data, email doesn't exist"]);
        }

        $stripe = new \Stripe\StripeClient(
            'sk_test_51ImJIiH1ST2SneRlI5texYpM1EjkRwX5h0sXH8lWH6BxPP2sFmNCXW3KqXvOCnVFnaKxOeSZd9ZhGqaYm2D1mVyl00xvAeAezq'
        );

        $account = $stripe->accounts->create([
            'type' => 'custom',
            'country' => 'FR',
            'email' => $parameters["email"],
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
        ]);*/

        /*$stripe = new \Stripe\StripeClient(
            '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'
        );
        */

        /*
        $bank_token = $stripe->tokens->create([
            'bank_account' => [
                'country' => 'FR',
                'currency' => 'eur',
                'account_holder_name' => 'Jenny Rosen',
                'account_holder_type' => 'individual',
                'account_number' => 'FR1420041010050500013M02606',
            ],
        ]);
        /*
        $bank_account = $stripe->accounts->createExternalAccount(
            'acct_1ImJIiH1ST2SneRl',
            [
                'external_account' => 'btok_1KufLlH1ST2SneRlGut0nLvV',
            ]
        );
        */

        \Stripe\Stripe::setApiKey('sk_test_51ImJIiH1ST2SneRlI5texYpM1EjkRwX5h0sXH8lWH6BxPP2sFmNCXW3KqXvOCnVFnaKxOeSZd9ZhGqaYm2D1mVyl00xvAeAezq');

        \Stripe\Charge::create(array(
            'currency' => 'eur',
            'amount'   => 10000,
            'card'     => 4000000000000077
        ));

        /*
        $response = \Stripe\OAuth::token([
            'grant_type' => 'authorization_code',
            'code' => 'ac_LfKkGVjObSFnDmwCAXJHwa7k5qp9mYU9',
        ]);
*/
// Access the connected account id in the response
        //$connected_account_id = $response->stripe_user_id;

        return new JsonResponse(['$connected_account_id'=> 'correct']);
    }


    /**
     * @Route("/transfer", name="transfer",methods={"POST"})
     */
    public function transfer(Request $request): Response
    {
        /*
        $parameters = json_decode($request->getContent(), true);
        if(!$parameters["stripe_account_id"]){
            return new BadResponseException(['message'=> "Wrong data, stripe_account_id doesn't exist"]);
        }

        $stripe = new \Stripe\StripeClient(
            'sk_test_51ImJIiH1ST2SneRlI5texYpM1EjkRwX5h0sXH8lWH6BxPP2sFmNCXW3KqXvOCnVFnaKxOeSZd9ZhGqaYm2D1mVyl00xvAeAezq'
        );
        $transfer = $stripe->transfers->create([
            'amount' => 2000,
            'currency' => 'usd',
            'destination' => $parameters["stripe_account_id"],
            'transfer_group' => 'ORDER_95',
        ]);
        */
        $stripe = new \Stripe\StripeClient(
            'sk_test_51ImJIiH1ST2SneRlI5texYpM1EjkRwX5h0sXH8lWH6BxPP2sFmNCXW3KqXvOCnVFnaKxOeSZd9ZhGqaYm2D1mVyl00xvAeAezq'
        );

        $transfer = $stripe->transfers->create([
            'amount' => 1,
            'currency' => 'eur',
            'destination' => 'acct_1Ky04NQZa3U3Hol5'
        ]);

        //acct_1Kxey9QiQedsUT5N
        return new JsonResponse(['$transfer'=> $transfer]);
    }
}
