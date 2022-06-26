<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\ElasticSearchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class SearchProductController extends AbstractController
{
    private ElasticSearchService $elasticSearchService;

    public function __construct(ElasticSearchService $elasticSearchService)
    {
        $this->elasticSearchService = $elasticSearchService;
    }

    /**
     * @Route("/products/search", name="products_search")
     */
    public function searchProductsFromElasticSearch(Request $request): Response
    {
        $word = $request->query->get('word') ?? null;
        $maxPrice = $request->query->get('maxPrice') ?? null;
        $minPrice = $request->query->get('minPrice') ?? null;
        $averageRatings = $request->query->get('averageRatings') ?? null;
        $region = $request->query->get('region') ?? null;
        $category = $request->query->get('category') ?? null;
        $page = $request->query->get('page') ?? null;
        $sort = $request->query->get('sort') ?? null;
        if($word === null && $maxPrice === null && $minPrice === null && $averageRatings === null && $region === null && $category === null && $page === null&& $sort === null){
           $keyword = [
                'body' => [
                    "from" => 15*($page-1),
                    "size" => 15,
                    "query" => [
                        "bool" => [
                            "must" => [
                                [
                                    'term' => [
                                        'is_valid' => true,
                                    ]
                                ],
                                [
                                    'term' => [
                                        'has_right' => true,
                                    ],
                                ],
                            ]
                        ]
                    ]
                ],
            ];
            $response = $this->elasticSearchService->getElasticClient()->search($keyword);
            $dataFormatted = json_decode(gzdecode($response));
            return new JsonResponse($dataFormatted);
        }

        if ($page !== null && $word !== null){
            $keyword = [
                'body' => [
                    "from" => 15*($page-1),
                    "size" => 15,
                    "query" => [
                        "bool" => [
                            "must_not" => [
                                [
                                    'term' => [
                                        'is_valid' => false,
                                    ]
                                ],
                                [
                                    'term' => [
                                        'has_right' => false,
                                    ],
                                ],
                            ],
                            "should" => [
                                [
                                    'match' => [
                                        'name' => $word
                                    ],
                                ],
                                [
                                    'match' => [
                                        'description' => $word
                                    ],
                                ],
                            ],
                        ]
                    ]
                ],
            ];
        }
        elseif($page !== null && $maxPrice !== null && $minPrice !== null && $region !== null && $category !== null){
            $keyword = [
                'body' => [
                    "from" => 15*($page-1),
                    "size" => 15,
                    "query" => [
                        "bool" => [
                            "must_not" => [
                                [
                                    'term' => [
                                        'is_valid' => false,
                                    ]
                                ],
                                [
                                    'term' => [
                                        'has_right' => false,
                                    ],
                                ],
                            ],
                            "filter" => [
                                [
                                    'range' => [
                                        'price' => [
                                            'gte' => $minPrice,
                                            'lte' => $maxPrice,
                                        ],
                                    ],
                                ],
                            ],
                            "must" => [
                                [
                                    'terms' => [
                                        'category' => $category
                                    ]
                                ],
                                [
                                    'terms' => [
                                        'region' => $region
                                    ]
                                ]
                            ],
                        ]
                    ]
                ],
            ];
        }elseif($page !== null && $maxPrice !== null && $minPrice !== null && $category !== null && $region === null){
            $keyword = [
                'body' => [
                    "from" => 15*($page-1),
                    "size" => 15,
                    "query" => [
                        "bool" => [
                            "must_not" => [
                                [
                                    'term' => [
                                        'is_valid' => false,
                                    ]
                                ],
                                [
                                    'term' => [
                                        'has_right' => false,
                                    ],
                                ],
                            ],
                            "filter" => [
                                [
                                    'range' => [
                                        'price' => [
                                            'gte' => $minPrice,
                                            'lte' => $maxPrice,
                                        ],
                                    ],
                                ],
                            ],
                            "must" => [
                                [
                                    'terms' => [
                                        'category' => $category
                                    ]
                                ],
                            ]
                        ]
                    ]
                ],
            ];
        }elseif($page !== null && $maxPrice !== null && $minPrice !== null && $region !== null && $category === null){
            $keyword = [
                'body' => [
                    "from" => 15*($page-1),
                    "size" => 15,
                    "query" => [
                        "bool" => [
                            "must_not" => [
                                [
                                    'term' => [
                                        'is_valid' => false,
                                    ]
                                ],
                                [
                                    'term' => [
                                        'has_right' => false,
                                    ],
                                ],
                            ],
                            "filter" => [
                                [
                                    'range' => [
                                        'price' => [
                                            'gte' => $minPrice,
                                            'lte' => $maxPrice,
                                        ],
                                    ],
                                ],
                            ],
                            "must" => [
                                [
                                    'terms' => [
                                        'region' => $region
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ];
        }elseif($page !== null && $maxPrice !== null && $minPrice !== null){
            $keyword = [
                'body' => [
                    "from" => 15*($page-1),
                    "size" => 15,
                    "query" => [
                        "bool" => [
                            "must_not" => [
                                [
                                    'term' => [
                                        'is_valid' => false,
                                    ]
                                ],
                                [
                                    'term' => [
                                        'has_right' => false,
                                    ],
                                ],
                            ],
                            "filter" => [
                                [
                                    'range' => [
                                        'price' => [
                                            'gte' => $minPrice,
                                            'lte' => $maxPrice,
                                        ],
                                    ],
                                ],
                            ]
                        ],
                    ]
                ],
            ];
        }else{
            $keyword = [
                'body' => [
                    "from" => 15*($page-1),
                    "size" => 15,
                    "query" => [
                        "bool" => [
                            "must" => [
                                [
                                    'term' => [
                                        'is_valid' => true,
                                    ]
                                ],
                                [
                                    'term' => [
                                        'has_right' => true,
                                    ],
                                ],
                            ],
                        ]
                    ]
                ],
            ];
        }

        if ($sort !== null){
            $sort =  [
                $sort[0] => [
                    "order" => $sort[1]
                ]
            ];
            $keyword['body']['sort'] =  $sort;
        }
        if ($averageRatings ==! "0"){
            $averageRatingsElem =  [
                'term' => [
                    'average_ratings' => $averageRatings,
                ],
            ];
            $keyword['body']['query']['bool']['must'] =  $averageRatingsElem;
        }
        $response = $this->elasticSearchService->getElasticClient()->search($keyword);
        $dataFormatted = json_decode(gzdecode($response));
        return new JsonResponse($dataFormatted);
    }
}
