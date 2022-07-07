<?php

namespace App\Controller;

use App\Manager\HasStripeAccountManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HasStripeAccount extends AbstractController
{
    protected $hasStripeAccountManager;
    public function __construct(HasStripeAccountManager $hasStripeAccountManager){
        $this->hasStripeAccountManager = $hasStripeAccountManager;
    }


    /**
     * @return ProductsNotValidManager
     */
    public function __invoke()
    {
        return $this->hasStripeAccountManager->checkStripeExternalAccount();
    }

}
