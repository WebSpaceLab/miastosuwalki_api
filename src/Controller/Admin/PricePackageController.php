<?php

namespace App\Controller\Admin;

use App\Controller\AbstractAPIController;
use App\Entity\Price;
use App\Entity\PricePackage;
use App\Repository\PricePackageRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/api/admin/price/package', name: 'app_admin_price_package')]
class PricePackageController extends AbstractAPIController
{
    #[Route('/{id}', name: ':create', methods: ['POST'])]
    public function create(Price $price, Request $request, ValidatorInterface $validator, PricePackageRepository $pricePackageRepository): JsonResponse
    {
        $pricePackage = $this->deserialize($request->getContent(), PricePackage::class, 'json', []);

        $errors = $validator->validate($pricePackage);
        
        if(count($errors) > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $pricePackage->setPriceList($price);
        $pricePackageRepository->save($pricePackage, true);

        $this->flash('Price list added successfully!');

        return $this->response([
            'pricePackage' => $pricePackage,
        ], ['admin:price:write'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: ':update', methods: ['PATCH'])]
    public function update(PricePackage $pricePackage, Request $request, ValidatorInterface $validator, PricePackageRepository $pricePackageRepository): JsonResponse
    {
        $pricePackage = $this->deserialize($request->getContent(), PricePackage::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $pricePackage,
        ]);

        $errors = $validator->validate($pricePackage);
        
        if(count($errors) > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $pricePackageRepository->save($pricePackage, true);

        $this->flash('Price package updated successfully!');

        return $this->response([
            'pricePackage' => $pricePackage,
        ], ['admin:price:write'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: ':delete', methods: ['DELETE'])]
    public function delete(PricePackageRepository $pricePackageRepository, PricePackage $pricePackage): JsonResponse
    {
        $pricePackage->setIsDelete(true);
        $pricePackageRepository->save($pricePackage, true);

        $this->flash('Price package deleted successfully!');
        
        return $this->response([]);
    }

    #[Route('/{id}/active', name: ':active', methods: ['PATCH'])]
    public function active(PricePackageRepository $pricePackageRepository, PricePackage $pricePackage): JsonResponse
    {
        $pricePackage->setIsActive(!$pricePackage->isIsActive());
        $pricePackageRepository->save($pricePackage, true);

        $this->flash('Price package active successfully!');
        
        return $this->response([]);
    }
}
