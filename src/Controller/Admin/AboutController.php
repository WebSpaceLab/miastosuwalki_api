<?php

namespace App\Controller\Admin;

use App\Controller\AbstractAPIController;
use App\Entity\About;
use App\Repository\AboutRepository;
use App\Repository\MediaRepository;
use App\Service\AboutHelper;
use App\Service\PaginationHelper;
use App\Service\QueryHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/api/admin/about', name: 'app_admin_about')]
class AboutController extends AbstractAPIController
{
    public function __construct(
        private PaginationHelper $paginationHelper, 
        private QueryHelper $QueryHelper,
        private AboutHelper $aboutHelper
    ) {}

    #[Route('', name: ':list', methods: ['GET'])]
    public function list(Request $request, AboutRepository $aboutRepository): JsonResponse
    {
        $query = $request->query->all();

        $queryBuilder = $aboutRepository->getWithSearchQueryBuilder(
            $query['term'], $query['orderBy'], $query['orderDir'], $query['status'], $query['month']
        );

        $pagination = $this->paginationHelper->paginate($queryBuilder, $query['page'], $query['per_page']);

        return $this->response([
            'about' => $pagination['data'],
            'pagination' => $pagination['pagination'],
            'queryParams' =>  $this->QueryHelper->params($request, ['term','status','month']),
            'status' => $this->aboutHelper->getActive(),
            'months' => $this->aboutHelper->getMonths(),
        ], ['admin:about:read']);
    }

    #[Route('', name: ':create', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator,  AboutRepository $aboutRepository, MediaRepository $mediaRepository): JsonResponse
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
        if($aboutRepository->findBy(['name' => $data['name']])) {
            return $this->json(['errors' => ['name' => 'Taka nazwa już istnieje.']], Response::HTTP_BAD_REQUEST);
        }

        $media = $mediaRepository->find($data['mediaId']);

        if(!$media) {
            return $this->json(['errors' => ['mediaId' => 'Nie znaleziono zdjęcia.']], Response::HTTP_BAD_REQUEST);
        }

        $about = new About();
        
        $about->setName($data['name']);
        $about->setDescription($data['description']);
        $about->setIsActive($data['isActive']);
        $about->setMedia($media);
        $about->setAuthor($this->getUser());
        
        try {
            $aboutRepository->save($about, true);

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
        
        $this->flash('Treść została dodana');

        return $this->response([
            'about' => $about,
        ], ['admin:about:read']);
    }

    #[Route('/{id}', name: ':update', methods: ['PATCH'])]
    public function update(About $about, Request $request, ValidatorInterface $validator,  AboutRepository $aboutRepository): JsonResponse
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

        $about->setName($data['name']);
        $about->setDescription($data['description']);
        $about->setIsActive($data['isActive']);
        
        $aboutRepository->save($about, true);

        $this->flash('Zmiana została zapisana.');

        return $this->response([
            'about' => $about,
        ], ['admin:about:read']);
    }

    #[Route('/{id}', name: ':delete', methods: ['DELETE'])]
    public function remove(About $about, AboutRepository $aboutRepository): JsonResponse
    {
        if (!$about) {
            return $this->json([
                'flash' => [
                    'type' => 'error',
                    'message' => 'Wiadomość nie istnieje.',
                ],
            ]);
        }

        $about->setIsDelete(true);

        $aboutRepository->save($about, true);

        return $this->json([
            'flash' => [
                'type' => 'success',
                'message' => 'Wiadomość została usunięta.',
            ],
        ], Response::HTTP_OK);
    }
}
