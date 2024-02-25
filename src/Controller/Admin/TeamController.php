<?php

namespace App\Controller\Admin;

use App\Controller\AbstractAPIController;
use App\Entity\Team;
use App\Repository\TeamRepository;
use App\Repository\MediaRepository;
use App\Service\TeamHelper;
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
#[Route('/api/admin/team', name: 'app_admin_team')]
class TeamController extends AbstractAPIController
{
    public function __construct(
        private PaginationHelper $paginationHelper, 
        private QueryHelper $QueryHelper,
        private TeamHelper $teamHelper
    ) {}

    #[Route('', name: ':list', methods: ['GET'])]
    public function list(Request $request, TeamRepository $teamRepository): JsonResponse
    {
        $query = $request->query->all();

        $queryBuilder = $teamRepository->getWithSearchQueryBuilder(
            $query['term'], $query['orderBy'], $query['orderDir'], $query['status'], $query['month']
        );

        $pagination = $this->paginationHelper->paginate($queryBuilder, $query['page'], $query['per_page']);

        return $this->response([
            'team' => $pagination['data'],
            'pagination' => $pagination['pagination'],
            'queryParams' =>  $this->QueryHelper->params($request, ['term', 'status','month']),
            'status' => $this->teamHelper->getActive(),
            'months' => $this->teamHelper->getMonths(),
        ], ['admin:team:read']);
    }

    #[Route('', name: ':create', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator,  TeamRepository $teamRepository, MediaRepository $mediaRepository): JsonResponse
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
        if($teamRepository->findBy(['name' => $data['name']])) {
            return $this->json(['errors' => ['name' => 'Taka nazwa już istnieje.']], Response::HTTP_BAD_REQUEST);
        }

        $media = $mediaRepository->find($data['mediaId']);

        if(!$media) {
            return $this->json(['errors' => ['mediaId' => 'Nie znaleziono zdjęcia.']], Response::HTTP_BAD_REQUEST);
        }

        $team = new Team();
        
        $team->setName($data['name']);
        $team->setDescription($data['description']);
        $team->setIsActive($data['isActive']);
        $team->setMedia($media);
        $team->setAuthor($this->getUser());
        
        try {
            $teamRepository->save($team, true);

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
        $this->flash('Osoba została dodana');
        
        return $this->response([
            'data' => $team,
        ], ['admin:team:read']);
    }

    #[Route('/{id}', name: ':update', methods: ['PATCH'])]
    public function update(Team $team, Request $request, ValidatorInterface $validator,  TeamRepository $teamRepository): JsonResponse
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

        $team->setName($data['name']);
        $team->setDescription($data['description']);
        $team->setIsActive($data['isActive']);
        
        $teamRepository->save($team, true);

        $this->flash('Zmiana została zapisana.');

        return $this->response([
            'team' => $team,
        ], ['admin:team:read']);
    }

    #[Route('/{id}', name: ':delete', methods: ['DELETE'])]
    public function remove(Team $team, TeamRepository $teamRepository): JsonResponse
    {
        if (!$team) {
            return $this->json([
                'flash' => [
                    'type' => 'error',
                    'message' => 'Grupa nie istnieje.',
                ],
            ]);
        }

        $team->setIsDelete(true);

        $teamRepository->save($team, true);

        return $this->json([
            'flash' => [
                'type' => 'success',
                'message' => 'Grupa została usunięta.',
            ],
        ], Response::HTTP_OK);
    }
}
