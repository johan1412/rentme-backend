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
            $url = getenv('RENTME_URL').'cancel';
            return $this->redirect($url);
        }

        $url = getenv('RENTME_URL').'success';
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
