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
            $url = getenv('RENTME_URL').'cancel';;
            return $this->redirect($url);
        }

        $url = getenv('RENTME_URL').'account';
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

        $url = getenv('RENTME_URL').'cancel';
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
        \Stripe\Stripe::setApiKey(getenv('STRIPE_KEY'));

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
        $url = getenv('RENTME_URL').'cancel';
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
            getenv('STRIPE_KEY')
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
     * @Route("/create-stripe-external-account", name="create_stripe_external",methods={"POST"})
     */
    public function createExternalStripeAccount(Request $request,EntityManagerInterface $em): Response
    {
        $parameters = json_decode($request->getContent(), true);
        if (! $parameters['renterId'] || !$parameters['code']){
            return new JsonResponse(['message'=>'Access denied'],403);
        }
        $user = $this->getUserRepository()->find($parameters['renterId']);
        if (!$user){
            return new JsonResponse(['message'=>'Access denied'],403);
        }

        if ($user->getStripeExternalAccount() || !in_array('ROLE_RENTER',$user->getRoles())){
            return new JsonResponse(['message'=>'Access denied'],403);
        }

        \Stripe\Stripe::setApiKey(getenv('STRIPE_KEY'));
        $response = \Stripe\OAuth::token([
            'grant_type' => 'authorization_code',
            'code' => $parameters['code'],
        ]);

        if (!$response->stripe_user_id){
            return new JsonResponse(['message'=>'Access denied'],403);
        }

        $user->setStripeExternalAccount($response->stripe_user_id);
        $em->persist($user);
        $em->flush();
        return new JsonResponse(['message'=>'creation stripe external account is successfully completed']);
    }

    /**
     * @Route("/transfer/{reservationId}", name="transfer",methods={"GET"})
     */
    public function transfer(Request $request,$reservationId,EntityManagerInterface $em): Response
    {
        if(!$reservationId){
            return new JsonResponse(['message'=>'Access denied'],403);
        }
        $reservation = $this->reservationRepository->find($reservationId);
        if (!$reservation){
            return new JsonResponse(['message'=>'Access denied'],403);
        }
        if ($reservation->getRenter()->getId() !== $this->getUser()->getId()){
            return new JsonResponse(['message'=>'Access denied'],403);
        }
        if ($reservation->getIsTransfered()){
            return new JsonResponse(['message'=>'You have already get transfered']);
        }
        if($reservation->getState() !== 'restored'){
            if ($reservation->getRentalEndDate() >= (new \DateTime())){
                return new JsonResponse(['message'=>'Deadline is not finished yet']);
            }
        }
        if (!$reservation->getRenter()->getStripeExternalAccount()){
            return new JsonResponse(['message'=>'You have not right for transfer'],403);
        }
        $stripe = new \Stripe\StripeClient(
            getenv('STRIPE_KEY')
        );

        $transfer = $stripe->transfers->create([
            'amount' => $reservation->getPaymentIntent() === 'success' ? ($reservation->getPrice()*80/100)*100 : ($reservation->getPrice()*80/100 + $reservation->getProduct()->getCaution())*100,
            'currency' => 'eur',
            'destination' => $reservation->getRenter()->getStripeExternalAccount()
        ]);

        $reservation->setIsTransfered(true);
        $em->persist($reservation);
        $em->flush();
        return new JsonResponse(['message'=>'Transfer is successfully completed']);
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
