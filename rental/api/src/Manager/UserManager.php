<?php


namespace App\Manager;


use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserManager
{
    protected $em;
    protected $passwordEncoder;
    protected $userRepository;

    public function __construct(EntityManagerInterface $em,UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
    }


    public function findEmail(string $email){
        return $this->userRepository->findOneByEmail($email);
    }
    public function registerAccount(User $user){

        if (!empty($this->findEmail($user->getEmail()))){
            throw new BadRequestHttpException("email exist already");
        }
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $user->getPassword()
            )
        );
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }
}