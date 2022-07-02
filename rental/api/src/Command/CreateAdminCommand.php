<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Region;
use App\Entity\Address;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateAdminCommand extends Command
{
    protected static $defaultName = 'app:create-admin';
    private EntityManagerInterface $em;
    private UserPasswordEncoderInterface $encoder;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        parent::__construct();

        $this->em = $em;
        $this->encoder = $encoder;
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates a new Admin.')
            ->setHelp('This command allows you to create an administrator...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Admin Creator',
            '============',
            '',
        ]);

        $tmpRegion = new Region();
        $tmpRegion->setName('tmp')->setNumber(25);
        $this->em->persist($tmpRegion);
        $this->em->flush();

        $tmpAdd = new Address();
        $tmpAdd->setCity("test");
        $tmpAdd->setStreetName('dsfsdf');
        $tmpAdd->setRegion($tmpRegion);

        $this->em->persist($tmpAdd);
        $this->em->flush();

        $helper = $this->getHelper('question');

        $question = new Question('Please enter the username [email] : ');
        $username = $helper->ask($input, $output, $question);

        $question = new Question('Please enter the password : ');
        $question->setHidden(true);
        $password = $helper->ask($input, $output, $question);

        $user = (new User())
            ->setEmail($username)
            ->setFirstName('admin')
            ->setLastName("admin")
            ->setRoles(['ROLE_ADMIN'])
            ->setAddress($tmpAdd)
            ->setIsVerified(true)
        ;
        $passwordEncoded = $this->encoder->encodePassword($user, $password);
        $user->setPassword($passwordEncoded);

        $this->em->persist($user);
        $this->em->flush();

        return Command::SUCCESS;
    }
}