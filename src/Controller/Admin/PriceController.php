<?php

namespace App\Controller\Admin;

use App\Controller\AbstractAPIController;
use App\Entity\Price;
use App\Repository\PriceRepository;
use App\Service\PaginationHelper;
use App\Service\PriceListHelper;
use App\Service\QueryHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/api/admin/price', name: 'app_admin_price')]
class PriceController extends AbstractAPIController
{
    public function __construct(
        private PaginationHelper $paginationHelper, 
        private QueryHelper $QueryHelper,
        private PriceListHelper $priceListHelper
    ) {}

    #[Route('', name: ':list', methods: ['GET'])]
    public function list(Request $request, PriceRepository $priceRepository): JsonResponse
    {
        $query = $request->query->all();

        $queryBuilder = $priceRepository->getWithSearchQueryBuilder(
            $query['term'], $query['orderBy'], $query['orderDir'], $query['status'], $query['month']
        );

        $pagination = $this->paginationHelper->paginate($queryBuilder, $query['page'], $query['per_page']);

        return $this->response([
            'price' => $pagination['data'],
            'pagination' => $pagination['pagination'],
            'queryParams' =>  $this->QueryHelper->params($request, ['term','status','month']),
            'status' => $this->priceListHelper->getActive(),
            'months' => $this->priceListHelper->getMonths(),
        ], ['admin:price:read']);
    }

    #[Route('', name: ':create', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator, PriceRepository $priceRepository): JsonResponse
    {
        $price = $this->deserialize($request->getContent(), Price::class, 'json', []);

        $errors = $validator->validate($price);
        
        if(count($errors) > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $priceRepository->save($price, true);

        $this->flash('Price list added successfully!');

        return $this->response([
            'price' => $price,
        ], ['admin:price:write'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: ':update', methods: ['PATCH'])]
    public function update(Price $price, Request $request, ValidatorInterface $validator, PriceRepository $priceRepository): JsonResponse
    {
        $price = $this->deserialize($request->getContent(), Price::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $price,
        ]);

        $errors = $validator->validate($price);
        
        if(count($errors) > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $priceRepository->save($price, true);

        $this->flash('Price list updated successfully!');

        return $this->response([
            'price' => $price,
        ], ['admin:price:write'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: ':delete', methods: ['DELETE'])]
    public function delete(PriceRepository $priceRepository, Price $price): JsonResponse
    {
        $price->setIsDelete(true);
        $priceRepository->save($price, true);

        $this->flash('Price list deleted successfully!');
        
        return $this->response([]);
    }

    #[Route('/{id}/active', name: ':active', methods: ['PATCH'])]
    public function active(PriceRepository $priceRepository, Price $price): JsonResponse
    {
        $price->setIsActive(!$price->isIsActive());
        $priceRepository->save($price, true);
        $active = $price->isIsActive() ? 'active' : 'inactive';
        $this->flash("Price list {$active} successfully!");
        
        return $this->response([]);
    }
}
