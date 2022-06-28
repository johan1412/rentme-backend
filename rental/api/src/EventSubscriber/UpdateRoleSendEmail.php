<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Product;
use App\Entity\Reservation;
use App\Entity\User;
use App\Security\EmailVerifier;
use App\Service\ElasticSearchService;
use Cassandra\Type\UserType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UpdateRoleSendEmail implements EventSubscriberInterface
{

    private TokenStorageInterface $tokenStorage;
    private EmailVerifier $emailVerifier;

    public function __construct(TokenStorageInterface $tokenStorage,EmailVerifier $emailVerifier)
    {
        $this->tokenStorage = $tokenStorage;
        $this->emailVerifier = $emailVerifier;
    }

    public static function getSubscribedEvents(): array
    {

        return [
            KernelEvents::VIEW => ['sendEmail', EventPriorities::PRE_WRITE],
        ];
    }

    public function sendEmail(ViewEvent $event)
    {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();


        if ($user instanceof User
            && Request::METHOD_PATCH === $method
        ) {
            if (!in_array('ROLE_RENTER',$user->getRoles())){
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $this->getUser(),
                    (new TemplatedEmail())
                        ->from(new Address('devfullstack44@gmail.com', 'Rentme Mail Bot'))
                        ->to($user->getEmail())
                        ->subject("Vous n'êtes plus loueur(se)")
                        ->htmlTemplate('role/not_have_role_renter.html.twig')
                        ->context([
                            'name' => $user->getFullName()
                        ])
                );
            }

            if (in_array('ROLE_RENTER',$user->getRoles())){
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $this->getUser(),
                    (new TemplatedEmail())
                        ->from(new Address('devfullstack44@gmail.com', 'Rentme Mail Bot'))
                        ->to($user->getEmail())
                        ->subject('Devenir loueur(se)')
                        ->htmlTemplate('role/have_role_renter.html.twig')
                        ->context([
                            'name' => $user->getFullName()
                        ])
                );
            }
        }
    }

    public function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }

}