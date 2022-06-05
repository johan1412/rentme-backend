<?php

// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController
{
/**
* @Route("/", name="app_lucky_number")
*/
public function number(): Response
{
$number = random_int(0, 10);

return new Response(
'<html><body>Lucky number: '.$number.'</body></html>'
);
}
}