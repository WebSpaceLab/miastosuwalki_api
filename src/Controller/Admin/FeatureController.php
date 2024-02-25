<?php

namespace App\Controller\Admin;

use App\Controller\AbstractAPIController;
use App\Entity\Feature;
use App\Repository\FeatureRepository;
use App\Repository\MediaRepository;
use App\Service\FeatureHelper;
use App\Service\PaginationHelper;
use App\Service\QueryHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/api/admin/feature', name: 'app_admin_feature')]
class FeatureController extends AbstractAPIController
{
    public function __construct(
        private PaginationHelper $paginationHelper, 
        private QueryHelper $QueryHelper,
        private FeatureHelper $featureHelper
    ) {}

    #[Route('', name: ':list', methods: ['GET'])]
    public function list(Request $request, FeatureRepository $featureRepository): JsonResponse
    {
        $query = $request->query->all();

        $queryBuilder = $featureRepository->getWithSearchQueryBuilder(
            $query['term'], $query['orderBy'], $query['orderDir'], $query['status'], $query['month']
        );

        $pagination = $this->paginationHelper->paginate($queryBuilder, $query['page'], $query['per_page']);

        return $this->response([
            'features' => $pagination['data'],
            'pagination' => $pagination['pagination'],
            'queryParams' =>  $this->QueryHelper->params($request, ['term','status','month']),
            'status' => $this->featureHelper->getActive(),
            'months' => $this->featureHelper->getMonths(),
        ], ['admin:feature:read']);
    }

    #[Route('', name: ':create', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator,  FeatureRepository $featureRepository, MediaRepository $mediaRepository): JsonResponse
    {
        $data = $request->toArray();

        $constraints = new Assert\Collection([
            'name' => [
                new NotBlank(),
                new Length(['min' => 2, 'minMessage' => 'Nazwa musi składać się z przynajmniej 2 liter.']),
            ],
            'description' => [
                new NotBlank(),
                new Length(['min' => 20, 'minMessage' => 'Nazwa musi składać się z przynajmniej 20 liter.']),
            ],
            'isActive' => [],
            'mediaId' => [
                new NotBlank(),
            ],
        ]);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $propertyPath = trim($violation->getPropertyPath(), '[\]');
                $errors[$propertyPath] = $violation->getMessage();
            }

            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        // return $this->json(['data' => $data], Response::HTTP_OK);
        if($featureRepository->findBy(['name' => $data['name']])) {
            return $this->json(['errors' => ['name' => 'Taka nazwa już istnieje.']], Response::HTTP_BAD_REQUEST);
        }

        $media = $mediaRepository->find($data['mediaId']);

        if(!$media) {
            return $this->json(['errors' => ['mediaId' => 'Nie znaleziono zdjęcia.']], Response::HTTP_BAD_REQUEST);
        }

        $feature = new Feature();
        
        $feature->setName($data['name']);
        $feature->setDescription($data['description']);
        $feature->setIsActive($data['isActive']);
        $feature->setMedia($media);
        $feature->setAuthor($this->getUser());
        
        try {
            $featureRepository->save($feature, true);

            $media->setIsUsed(true);
            $mediaRepository->save($media, true);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->json(['flash' => [
                    'message' => $th,
                    'type' => 'error'
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->flash('Feature zostało dodane');

        return $this->response([
            'data' => $feature,
        ], ['admin:feature:read']);
    }

    #[Route('/{id}', name: ':update', methods: ['PATCH'])]
    public function update(Feature $feature, Request $request, ValidatorInterface $validator,  FeatureRepository $featureRepository): JsonResponse
    {
        $data = $request->toArray();

        $constraints = new Assert\Collection([
            'name' => [
                new NotBlank(),
                new Length(['min' => 2, 'minMessage' => 'Nazwa musi składać się z przynajmniej 2 liter.']),
            ],
            'description' => [
                new NotBlank(),
                new Length(['min' => 20, 'minMessage' => 'Nazwa musi składać się z przynajmniej 20 liter.']),
            ],
            'isActive' => [],
            'mediaId' => [
                new NotBlank(),
            ],
        ]);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $propertyPath = trim($violation->getPropertyPath(), '[\]');
                $errors[$propertyPath] = $violation->getMessage();
            }

            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $feature->setName($data['name']);
        $feature->setDescription($data['description']);
        $feature->setIsActive($data['isActive']);
        
        $featureRepository->save($feature, true);

        $this->flash('Zmiana została zapisana.');

        return $this->response([
            'feature' => $feature,
        ], ['admin:feature:read']);
    }

    #[Route('/{id}', name: ':delete', methods: ['DELETE'])]
    public function remove(Feature $feature, FeatureRepository $featureRepository): JsonResponse
    {
        if (!$feature) {
            return $this->json([
                'flash' => [
                    'type' => 'error',
                    'message' => 'Wiadomość nie istnieje.',
                ],
            ]);
        }

        $feature->setIsDelete(true);

        $featureRepository->save($feature, true);

        return $this->json([
            'flash' => [
                'type' => 'success',
                'message' => 'Wiadomość została usunięta.',
            ],
        ], Response::HTTP_OK);
    }
}
