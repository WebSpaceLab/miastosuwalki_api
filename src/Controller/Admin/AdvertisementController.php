<?php

namespace App\Controller\Admin;

use App\Controller\AbstractAPIController;
use App\Entity\Advertisement;
use App\Entity\User;
use App\Repository\AdvertisementRepository;
use App\Repository\MediaRepository;
use App\Service\AdvertisementHelper;
use App\Service\PaginationHelper;
use App\Service\QueryHelper;
use App\Service\UploaderHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/api/admin/advertisements', name: 'app_admin_advertisements')]
class AdvertisementController extends AbstractAPIController
{
    public function __construct(
        private PaginationHelper $paginationHelper, 
        private QueryHelper $QueryHelper,
        private AdvertisementHelper $advertisementHelper
    ) {}


    #[Route('', name: ':list', methods: ['GET'])]
    public function list(Request $request, AdvertisementRepository $advertisementRepository): JsonResponse
    {
        $query = $request->query->all();

        $queryBuilder = $advertisementRepository->getWithSearchQueryBuilder(
            $query['term'], $query['orderBy'], $query['orderDir'], $query['status'], $query['month']
        );

        $pagination = $this->paginationHelper->paginate($queryBuilder, $query['page'], $query['per_page']);

        return $this->response([
            'advertisements' => $pagination['data'],
            'pagination' => $pagination['pagination'],
            'queryParams' =>  $this->QueryHelper->params($request, ['term','status','month']),
            'status' => $this->advertisementHelper->getActive(),
            'months' => $this->advertisementHelper->getMonths(),
        ], ['admin:advertisement:read']);
    }


    #[Route('', name: ':create', methods: ['POST'])]
    public function create(Request $request, AdvertisementRepository $advertisementRepository, #[CurrentUser()] User $user, UploaderHelper $uploaderHelper, ValidatorInterface $validator): JsonResponse
    {
        $data = $request->toArray();
        $advertisement = $this->deserialize($request->getContent(), Advertisement::class, 'json', []);

        $violations = $validator->validate($advertisement);
        
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $propertyPath = trim($violation->getPropertyPath(), '[\]');
                $errors[$propertyPath] = $violation->getMessage();
            }

            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }
        
        if($data) {
            $advertisement->setAdding($user);
            $advertisement->setClicks(0);

            $file = $request->files->get('file');

            $filename = $uploaderHelper->uploadImage($file, $this->getUploadsDir());

            if(!$filename) {
                return $this->json([
                    'flash' => [
                        'message' => 'Coś poszło nie tak. Plik nie został przesłany.',
                        'type' => 'error'
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            $advertisement->setImageUrl('/uploads/advertisements/' . $filename);

            $advertisementRepository->save($advertisement, true);

            $this->flash('The advertisement has been added.');

            return $this->response(['advertisement' => $advertisement], ['admin:advertisement:read']);
        }

        return $this->json([
            'flash' => [
                'message' => 'Coś poszło nie tak. Plik nie został przesłany.',
                'type' => 'error'
            ],
        ], Response::HTTP_BAD_REQUEST);
    }

    private function getUploadsDir()
    {
        return $this->getParameter('uploads_dir').'/advertisements/';
    }
}
