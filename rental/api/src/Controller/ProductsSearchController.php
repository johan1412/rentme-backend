<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Product;
use App\Manager\SearchProductsManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductsSearchController extends AbstractController
{

    public function searchProducts(ManagerRegistry $doctrine, Request $req): Response
    {
        $params = json_decode($req->getContent());
        $conditions = ['p.isValid = true' ];

        $em = $doctrine->getManager();

        $queryText = 'SELECT p.id, p.name, p.description, p.averageRatings,
         r.number as region, r.name as regionName, 
         p.price, f.path
         FROM App\Entity\Product p
         JOIN APP\Entity\Category c WITH c.id = p.category
         JOIN APP\Entity\Address a WITH a.id = p.address
         JOIN APP\Entity\Region r WITH r.id = a.region
         LEFT JOIN p.files f
        ';


        $page = $params->page ?? 1;

        if(isset($params->sort)){
           switch ($params->sort){
               case "ratingAsc" :
                   $sort = " p.averageRatings ASC ";
                   $conditions[] = " p.averageRatings IS NOT NULL ";
                   break;
               case "ratingDesc" :
                   $sort = " p.averageRatings DESC ";
                   $conditions[] = " p.averageRatings IS NOT NULL ";
                   break;
               case "priceAsc" :
                   $sort = " p.price ASC ";
                   break;
               case "priceDesc" :
                   $sort = " p.price DESC ";
                   break;
               default : $sort = " p.id DESC ";
           }
        }else{
            $sort = " p.id DESC ";
        }
        $sort = " ORDER BY ".$sort;


        if(isset($params->category)){
            $conditions[] = 'p.category IN ('.implode(',',$params->category).")";
        }

        if(isset($params->minPrice)){
            $conditions[] = 'p.price >= '.$params->minPrice;
        }

        if(isset($params->maxPrice)){
            $conditions[] = 'p.price <= '.$params->maxPrice;
        }

        if(isset($params->region)){
            $conditions[] = 'r.id IN ('.implode(',',$params->region).")";
        }

        if(isset($params->averageRatings)){
            $conditions[] = 'p.averageRatings >= '.$params->averageRatings;
        }

       // $sort = " ORDER BY p.id ASC ";
        $queryText .= " WHERE ".implode(" AND ", $conditions).$sort;


        $query = $em->createQuery(
            $queryText
        )->setFirstResult(($page-1)*15)
            ->setMaxResults(15);
        $products = $query->getArrayResult();

        return new JsonResponse($products);
    }

}
