<?php

namespace App\Controller\Admin;

use App\Controller\AbstractAPIController;
use App\Entity\PricePackage;
use App\Entity\PricePackageInformation;
use App\Repository\PricePackageInformationRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/api/admin/price/package/information', name: 'app_admin_price_package_information')]
class PricePackageInformationController extends AbstractAPIController
{
    #[Route('/{id}', name: ':create', methods: ['POST'])]
    public function create(PricePackage $pricePackage, Request $request, ValidatorInterface $validator, PricePackageInformationRepository $pricePackageInformationRepository): JsonResponse
    {
        $pricePackageInformation = $this->deserialize($request->getContent(), PricePackageInformation::class, 'json', []);

        $errors = $validator->validate($pricePackageInformation);
        
        if(count($errors) > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $pricePackageInformation->setPricePackage($pricePackage);
        $pricePackageInformationRepository->save($pricePackageInformation, true);

        $this->flash('Price list added successfully!');

        return $this->response([
            'pricePackage' => $pricePackage,
        ], ['admin:price:write'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: ':update', methods: ['PATCH'])]
    public function update(PricePackageInformation $pricePackageInformation, Request $request, ValidatorInterface $validator, PricePackageInformationRepository $pricePackageInformationRepository): JsonResponse
    {
        $pricePackageInformation = $this->deserialize($request->getContent(), PricePackageInformation::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $pricePackageInformation,
        ]);

        $errors = $validator->validate($pricePackageInformation);
        
        if(count($errors) > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $pricePackageInformationRepository->save($pricePackageInformation, true);

        $this->flash('Price package updated successfully!');

        return $this->response([
            'pricePackageInformation' => $pricePackageInformation,
        ], ['admin:price:write'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: ':delete', methods: ['DELETE'])]
    public function delete(PricePackageInformationRepository $pricePackageInformationRepository, PricePackageInformation $pricePackageInformation): JsonResponse
    {
        $pricePackageInformationRepository->remove($pricePackageInformation, true);

        $this->flash('Price package deleted successfully!');

        return $this->response([], [], Response::HTTP_OK);
    }
}
