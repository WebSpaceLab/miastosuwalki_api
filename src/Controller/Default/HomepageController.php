<?php

namespace App\Controller\Default;

use App\Controller\AbstractAPIController;
use App\Repository\AboutRepository;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractAPIController
{
    #[Route('/', name: 'app_homepage:index', methods: ['GET'])]
    public function index()
    {
        return new Response('<html><body><h1>Homepage index.</h1></body></html>');
    }

    #[Route('/api/homepage', name: 'app_homepage:home', methods: ['GET'])]
    public function home(ArticleRepository $articleRepository, AboutRepository $aboutRepository): JsonResponse
    {
        $hero = [
            $articleRepository->findLatestArticlesByCategory('Miasto', 1)[0] ?? null,
            $articleRepository->findLatestArticlesByCategory('Historia', 1)[0] ?? null,
            $articleRepository->findLatestArticlesByCategory('Sport', 1)[0] ?? null,    
            $articleRepository->findLatestArticlesByCategory('Technologia', 1)[0] ?? null,
            $articleRepository->findLatestArticlesByCategory('Natura', 1)[0] ?? null,
        ];
        $about =  $aboutRepository->getActiveAbout();

        return $this->response([
            'hero' => $hero,
            'latest' => $articleRepository->findLatestArticles() ?? null,
            'town' => $articleRepository->findLatestArticlesByCategory('Miasto') ?? null,
            'history' => $articleRepository->findLatestArticlesByCategory('Historia') ?? null,
            'about' => $about,
        ], ['homepage:read']);
    }
}
