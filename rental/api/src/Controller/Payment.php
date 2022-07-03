<?php

namespace App\Controller;


use App\Entity\Product;
use App\Entity\Reservation;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

Class Payment extends AbstractController{


    private $productRepository;
    private $userRepository;
    private $reservationRepository;
    private EmailVerifier $emailVerifier;

    public function __construct(ProductRepository $productRepository,UserRepository $userRepository,ReservationRepository $reservationRepository,EmailVerifier $emailVerifier)
    {
        $this->productRepository = $productRepository;
        $this->userRepository = $userRepository;
        $this->reservationRepository = $reservationRepository;
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/success/{tok_stripe}/{id}", name="success",methods={"GET"})
     */
    public function success(Request $request,$tok_stripe,$id): Response
    {
        $user = $this->getUserRepository()->find($id);

        $databaseToken = $user->getStripeToken();
        $user->setStripeToken(null);

        if ($tok_stripe != $databaseToken) {
            $url = 'http://localhost:8080/cancel';
            return $this->redirect($url);
        }

        $url = 'http://localhost:8080/success';
        return $this->redirect($url);
    }

    /**
     * @Route("/error", name="error",methods={"GET"})
     */
    public function error(): Response
    {
        return new JsonResponse(["Failed payment"]);
    }

    /**
     * @Route("/create-checkout-session/{productId}/{tenantId}", name="checkout",methods={"POST"})
     */
    public function checkout(Request $request,$productId,$tenantId,EntityManagerInterface $em): Response
    {

        $stripeToken = strval(random_int(0,10000000000));
        $user = $this->getUser();
        $user->setStripeToken($stripeToken);
        $em->persist($user);
        $em->flush();

        $url = 'http://localhost:8080/cancel';
        $parameters = json_decode($request->getContent(), true);
        if(!$productId || !$tenantId || !$parameters['price']){
            return $this->redirect($url);
        }

        $product = $this->getProductRepository()->find($productId);
        if(!$product || !$product->getIsValid()){
            return $this->redirect($url);
        }

        $tenant = $this->getUserRepository()->find($tenantId);
        if(!$tenant){
            return $this->redirect($url);
        }

        // This is your test secret API key.
        \Stripe\Stripe::setApiKey('sk_test_51ImJIiH1ST2SneRlI5texYpM1EjkRwX5h0sXH8lWH6BxPP2sFmNCXW3KqXvOCnVFnaKxOeSZd9ZhGqaYm2D1mVyl00xvAeAezq');

        $checkout_session = \Stripe\Checkout\Session::create([
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $product->getName(),
                        ],
                        'unit_amount' => $parameters['price']*100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $this->generateUrl('success',['tok_stripe' => $stripeToken,'id' => $user->getId()],UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('error',[],UrlGeneratorInterface::ABSOLUTE_URL),
            ]);
        return new JsonResponse(['checkout_session'=>$checkout_session]);
    }


    /**
     * @Route("/refund/{reservationId}", name="refund",methods={"GET"})
     */
    public function refund(Request $request, $reservationId): Response
    {
        $url = 'http://localhost:8080/cancel';
        if(!$reservationId){
            return $this->redirect($url);
        }
        $reservation = $this->reservationRepository->find($reservationId);
        if (!$reservation){
            return new JsonResponse(['message'=>'Refund is failed']);
        }
        if ($reservation->getRentalEndDate() < (new \DateTime())){
            return new JsonResponse(['message'=>'Refund not possible']);
        }
        $stripe = new \Stripe\StripeClient(
            'sk_test_51ImJIiH1ST2SneRlI5texYpM1EjkRwX5h0sXH8lWH6BxPP2sFmNCXW3KqXvOCnVFnaKxOeSZd9ZhGqaYm2D1mVyl00xvAeAezq'
        );
        $stripe->refunds->create([
            'payment_intent' => $reservation->getPaymentIntent(),
            'amount' => $reservation->getProduct()->getCaution()*100
        ]);

        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $reservation->getTenant(),
            (new TemplatedEmail())
                ->from(new Address('devfullstack44@gmail.com', 'Rentme Mail Bot'))
                ->to($reservation->getTenant()->getEmail())
                ->subject('Remboursement products')
                ->htmlTemplate('refund/tenant.html.twig')
                ->context([
                    'tenant' => $reservation->getTenant()->getFullName(),
                    'product' => $reservation->getProduct()->getName(),
                    'caution' => $reservation->getProduct()->getCaution(),
                ])
        );

        return new JsonResponse(['message'=>'Refund is successfully completed']);
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

        $stripe = \Stripe\Stripe::setApiKey('sk_test_51ImJIiH1ST2SneRlI5texYpM1EjkRwX5h0sXH8lWH6BxPP2sFmNCXW3KqXvOCnVFnaKxOeSZd9ZhGqaYm2D1mVyl00xvAeAezq');

       /* \Stripe\Charge::create(array(
            'currency' => 'eur',
            'amount'   => 10000,
            'card'     => 4000000000000077
        ));*/

        /*
        $response = \Stripe\OAuth::token([
            'grant_type' => 'authorization_code',
            'code' => 'ac_LfKkGVjObSFnDmwCAXJHwa7k5qp9mYU9',
        ]);
*/
// Access the connected account id in the response
        //$connected_account_id = $response->stripe_user_id;

        $id_account = $stripe->accounts->retrieve(
            [
                "type" => "express",
                "object" => "account",
                'email' => 'abdellatifchalala44@gmail.com']
        );
        return new JsonResponse(['account'=> $id_account]);
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

    /**
     * @return ProductRepository
     */
    public function getProductRepository(): ProductRepository
    {
        return $this->productRepository;
    }

    /**
     * @return UserRepository
     */
    public function getUserRepository(): UserRepository
    {
        return $this->userRepository;
    }



}
