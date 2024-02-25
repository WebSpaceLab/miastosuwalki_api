<?php

namespace App\Controller\Admin;

use App\Controller\AbstractAPIController;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\CategoriesHelper;
use App\Service\PaginationHelper;
use App\Service\QueryHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/api/admin/categories', name: 'app_admin_categories')]
class CategoryController extends AbstractAPIController
{
    public function __construct(
        private PaginationHelper $paginationHelper, 
        private QueryHelper $QueryHelper,
        private CategoriesHelper $categoriesHelper
    ) {}

    #[Route('', name: ':index', methods: ['GET'])]
    public function index(Request $request, CategoryRepository $categoryRepository): JsonResponse
    {
        $query = $request->query->all();

        $queryBuilder = $categoryRepository->getWithSearchQueryBuilder(
            $query['term'], $query['orderBy'], $query['orderDir'], $query['status'], $query['month']
        );

        $pagination = $this->paginationHelper->paginate($queryBuilder, $query['page'], $query['per_page']);

        return $this->response([
            'categories' => $pagination['data'],
            'pagination' => $pagination['pagination'],
            'queryParams' =>  $this->QueryHelper->params($request, ['term','status','month']),
            'status' => $this->categoriesHelper->getActive(),
            'months' => $this->categoriesHelper->getMonths(),
        ], ['admin:category:read']);
    }

    #[Route('', name: ':create', methods: ['POST'])]
    public function create(CategoryRepository $categoryRepository, ValidatorInterface $validator, Request $request): JsonResponse
    {
        $category = $this->deserialize($request->getContent(), Category::class, 'json', []);

        $errors = $validator->validate($category);
        
        if(count($errors) > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $categoryRepository->save($category, true);

        $this->flash('Kategoria została dodana');
       
        return $this->response($category,['category:read'], Response::HTTP_CREATED);
    }

    #[Route('/{slug}', name: '.show', methods: ['GET'])]
    public function show(Category $category): JsonResponse
    {
        return $this->response($category, ['category:write']);
    }

    #[Route('/{id}', name: ':update', methods: ['PATCH'])]
    public function update(CategoryRepository $categoryRepository,  Category $category, ValidatorInterface $validator, Request $request): JsonResponse
    {
        $category = $this->deserialize($request->getContent(), Category::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $category,
        ]);

        $errors = $validator->validate($category);
        
        if(count($errors) > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $categoryRepository->save($category, true);

        $this->flash('Kategoria została zaktualizowana');
        
        return $this->response([
            'category' => $category
        ], ['category:read']);
    }

    #[Route('/{id}', name: ':delete', methods: ['DELETE'])]
    public function delete(CategoryRepository $categoryRepository,  Category $category): JsonResponse
    {
        $category->setIsDelete(true);
        $categoryRepository->save($category, true);

        return $this->json([
            'flash' => [
                'type' => 'success',
                'message' => 'Kategoria została usunięta'
            ]
        ], Response::HTTP_NO_CONTENT);
    }
}
