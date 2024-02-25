<?php

namespace App\Controller\Default;

use App\Controller\AbstractAPIController;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/categories', name: 'app_categories')]
class CategoryController extends AbstractAPIController
{
    #[Route('', name: ':index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): JsonResponse
    {
        $categories = $categoryRepository->getActiveCategories();

        return $this->response([
            'categories' => $categories
        ], ['editor:category:read']);
    }
}
