<?php

namespace App\Controller\Default;

use App\Controller\AbstractAPIController;
use App\Entity\Article;
use App\Entity\Category;
use App\Repository\ArticleRepository;
use App\Service\ArticleHelper;
use App\Service\PaginationHelper;
use App\Service\QueryHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/api/articles', name: 'app_article')]
class ArticleController extends AbstractAPIController
{
    public function __construct(
        private PaginationHelper $paginationHelper, 
        private QueryHelper $QueryHelper,
        private ArticleHelper $articleHelper
    ) {}

    #[Route('', name: ':index', methods: ['GET'])]
    public function index(Request $request, ArticleRepository $articleRepository): JsonResponse
    {
        $query = $request->query->all();

        $queryBuilder = $articleRepository->getWithSearchQueryBuilderOnlyPublished(
            $query['term'], $query['orderBy'], $query['orderDir'], $query['month']
        );

        $pagination = $this->paginationHelper->paginate($queryBuilder, $query['page'], $query['per_page']);

        return $this->response([
            'articles' => $pagination['data'],
            'pagination' => $pagination['pagination'],
            'queryParams' =>  $this->QueryHelper->params($request, ['term','month']),
            'months' => $this->articleHelper->getMonths(),
        ], ['article:show']);
    }

    #[Route('/{slug}/categories', name: ':category', methods: ['GET'])]
    public function category(Category $category, Request $request, ArticleRepository $articleRepository): JsonResponse
    {
        $query = $request->query->all();

        $queryBuilder = $articleRepository->getWithSearchQueryBuilderOnlyPublishedForCategory(
            $category, $query['term'], $query['orderBy'], $query['orderDir'], $query['month']
        );

        $pagination = $this->paginationHelper->paginate($queryBuilder, $query['page'], $query['per_page']);

        return $this->response([
            'category' => $category,
            'articles' => $pagination['data'],
            'pagination' => $pagination['pagination'],
            'queryParams' =>  $this->QueryHelper->params($request, ['term','month']),
            'months' => $this->articleHelper->getMonths(),
        ], ['article:show']);
    }

    #[Route('/{slug}', name: ':show', methods: ['GET'])]
    public function show(Article $article, ArticleRepository $articleRepository): JsonResponse
    {
        return $this->response([            
            'article' => $article,
            'latest' => $articleRepository->findLatestArticles()
        ], ['article:show']);
    }
}
