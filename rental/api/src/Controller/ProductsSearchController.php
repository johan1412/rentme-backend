<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductsSearchController extends AbstractController
{

    public function getProductsBySubCategory(ManagerRegistry $doctrine, $id)
    {
        $subCategoryId = intval($id);

        if ($subCategoryId == null) {
            return new JsonResponse(['error' => 'No subCategoryId provided'], Response::HTTP_BAD_REQUEST);
        }

        $query = $doctrine->getManager()->createQuery(
          'SELECT p.id, p.name, p.averageRatings, p.numbersOfRatings,
          r.number as region, r.name as regionName,
          p.price, f.path, a.city as city, p.isValid
          FROM App\Entity\Product p
          JOIN APP\Entity\Category c WITH c.id = p.category
          JOIN APP\Entity\Address a WITH a.id = p.address
          JOIN APP\Entity\Region r WITH r.id = a.region
          LEFT JOIN p.files f
          WHERE c.id = :subCategoryId'
        )->setParameter('subCategoryId', $subCategoryId)
        ->setMaxResults(10);

        // returns an array of Product objects
        $products = $query->getArrayResult();

        return new JsonResponse(array('products' => $products));
    }

    public function searchProducts(ManagerRegistry $doctrine, Request $req): Response
    {

      $page_recu = $req->query->get('page') ?? null;
      $sort_recu = $req->query->get('sort') ?? null;
      $category_recu = $req->query->get('category') ?? [];
      $minPrice_recu = $req->query->get('minPrice') ?? null;
      $maxPrice_recu = $req->query->get('maxPrice') ?? null;
      $region_recu = $req->query->get('region') ?? [];
      $averageRatings_recu = $req->query->get('averageRatings') ?? null;

      $conditions = ['p.isValid = true'];

      $em = $doctrine->getManager();

      $queryText = 'SELECT p.id, p.name, p.description, p.averageRatings, p.numbersOfRatings,
      r.number as region, r.name as regionName,
      p.price, f.path, a.city as city
      FROM App\Entity\Product p
      JOIN APP\Entity\Category c WITH c.id = p.category
      JOIN APP\Entity\Address a WITH a.id = p.address
      JOIN APP\Entity\Region r WITH r.id = a.region
      LEFT JOIN p.files f
      ';

      $page = $page_recu ?? 1;

      if(isset($sort_recu) && !is_null($sort_recu) && !empty($sort_recu)) {
        switch ($sort_recu) {
          case "ratingAsc" :
            $sort = ' p.averageRatings ASC ';
            $conditions[] = ' p.averageRatings IS NOT NULL ';
            break;
          case 'ratingDesc' :
            $sort = ' p.averageRatings DESC ';
            $conditions[] = ' p.averageRatings IS NOT NULL ';
            break;
          case 'priceAsc' :
            $sort = " p.price ASC ";
            break;
          case 'priceDesc' :
            $sort = ' p.price DESC ';
            break;
          default : $sort = ' p.id DESC ';
        }
      } else {
        $sort = ' p.id DESC ';
      }
      $sort = ' ORDER BY ' . $sort;


      if(isset($category_recu) && !is_null($category_recu) && !empty($category_recu)) {
          $conditions[] = "p.category IN (". implode(", ", $category_recu) . ")";
      }

      if(isset($minPrice_recu) && !is_null($minPrice_recu) && $minPrice_recu > 0) {
          $conditions[] = 'p.price >= ' . $minPrice_recu;
      }

      if(isset($maxPrice_recu) && !is_null($maxPrice_recu) && $maxPrice_recu > 0) {
          $conditions[] = 'p.price <= ' . $maxPrice_recu;
      }

      if(isset($region_recu) && !is_null($region_recu) && !empty($region_recu)) {
          $conditions[] = 'r.id IN ('. implode(", ", $region_recu) . ')';
      }

      if(isset($averageRatings_recu) && !is_null($averageRatings_recu) && $averageRatings_recu > 0) {
          $conditions[] = 'p.averageRatings >= ' . $averageRatings_recu;
      }

      $queryText .= " WHERE ". implode(' AND ', $conditions) . $sort;

      $query = $em->createQuery(
          $queryText
      )
      ->setFirstResult(($page-1)*15)
      ->setMaxResults(15);
      $products = $query->getArrayResult();

      $query2 = $em->createQuery(
        $queryText
      )
      ->getArrayResult();

      $results = array('products' => $products, 'totalProducts' => count($query2));

      return new JsonResponse($results);
    }

}
