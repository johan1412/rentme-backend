<?php


namespace App\Controller;


use App\Manager\UserManager;

class CreateUser
{
    protected $userManager;
    public function __construct(UserManager $userManager){
        $this->userManager = $userManager;
    }

    /**
     * @return UserManager
     */
    public function __invoke($data)
    {
        $this->userManager->registerAccount($data);
    }

}