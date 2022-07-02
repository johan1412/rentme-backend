<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Reservation;
use App\Entity\User;
use App\Security\EmailVerifier;
use Cassandra\Type\UserType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mime\Address;

class EmailReservation implements EventSubscriberInterface
{

    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    public static function getSubscribedEvents(): array
    {

        return [
            KernelEvents::VIEW => ['sendMail', EventPriorities::POST_WRITE],
        ];
    }

    public function sendMail(ViewEvent $event)
    {
        $reservation = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();


        if ($reservation instanceof Reservation
            && Request::METHOD_POST === $method
        ) {
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $reservation->getRenter(),
                (new TemplatedEmail())
                    ->from(new Address('devfullstack44@gmail.com', 'Rentme Mail Bot'))
                    ->to($reservation->getRenter()->getEmail())
                    ->subject('Reservation products')
                    ->htmlTemplate('reservation/renter.html.twig')
                    ->context([
                        'rentalBeginDate' => $reservation->getRentalEndDate()->format('d/m/Y'),
                        'rentalEndDate' => $reservation->getRentalEndDate()->format('d/m/Y'),
                        'tenant' => $reservation->getTenant()->getFullName(),
                        'product' => $reservation->getProduct()->getName(),
                    ])
            );

            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $reservation->getTenant(),
                (new TemplatedEmail())
                    ->from(new Address('devfullstack44@gmail.com', 'Rentme Mail Bot'))
                    ->to($reservation->getTenant()->getEmail())
                    ->subject('Reservation products')
                    ->htmlTemplate('reservation/tenant.html.twig')
                    ->context([
                        'rentalBeginDate' => $reservation->getRentalEndDate()->format('d/m/Y'),
                        'rentalEndDate' => $reservation->getRentalEndDate()->format('d/m/Y'),
                        'product' => $reservation->getProduct()->getName(),
                        'price' => $reservation->getPrice(),
                    ])
            );
        }
    }

}