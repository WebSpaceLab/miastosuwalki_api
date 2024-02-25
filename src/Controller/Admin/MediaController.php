<?php

namespace App\Controller\Admin;

use App\Controller\AbstractAPIController;
use App\Entity\Media;
use App\Entity\User;
use App\Repository\MediaRepository;
use App\Service\MediaHelper;
use App\Service\PaginationHelper;
use App\Service\UploaderHelper;
use App\Service\QueryHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/media', name: 'app_admin_media')]
#[isGranted('ROLE_ADMIN')]
class MediaController extends AbstractAPIController
{
    public function __construct(
        private PaginationHelper $paginationHelper, 
        private MediaHelper $mediaHelper, 
        private QueryHelper $QueryHelper
    ) {}

    #[Route('', name: ':index', methods: ['GET'])]
    public function list(Request $request, MediaRepository $mediaRepository, #[CurrentUser()] User $user,): JsonResponse
    {
        $query = $request->query->all();

        $queryBuilder = $mediaRepository->getWithSearchQueryBuilder(
            $query['term'], $query['orderBy'], $query['orderDir'], $query['fileType'], $query['month']
        );

        $this->mediaHelper->changePreviewUrlsType($queryBuilder);

        $pagination = $this->paginationHelper->paginate($queryBuilder, $query['page'], $query['per_page']);

        return $this->response([
            'media' => $pagination['data'],
            'pagination' => $pagination['pagination'],
            'queryParams' =>  $this->QueryHelper->params($request, ['term','fileType','month']),
            'months' => $this->mediaHelper->getMonths(),
            'fileTypes' => $this->mediaHelper->getFileTypes(),
        ], ['admin:media:read']);
    }

    #[Route('/{id}', name: ':update', methods: ['POST'])]
    public function update(Media $media, Request $request,  UploaderHelper $uploaderHelper, MediaRepository $mediaRepository)
    {
        $file = $request->files->get('file');

        $this->validate([
            'file' => [
                new NotBlank(),
                new File()
            ],
        ], $file);

        if(is_null($media)) {
            return $this->json([
                'flash' => [
                    'message' => 'Nie znaleziono pliku.',
                    'type' => 'error'
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        if($file) {
            $media->setName($file->getClientOriginalName());
            $media->setMimeType($file->getMimeType());
            $media->setSize($file->getSize());
            
            $filename = $uploaderHelper->uploadImage($file, $this->getUploadsDir());

            if(!$filename) {
                return $this->json([
                    'flash' => [
                        'message' => 'Coś poszło nie tak. Plik nie został przesłany.',
                        'type' => 'error'
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

            $media->setFileName($filename);
            $media->setFilePath('/uploads/' .  $filename);
            $mediaRepository->save($media, true);

            $this->flash('The file has been updated.');

            return $this->response(['media' => $media], ['admin:media:read']);
        }
        
        return $this->json([
            'flash' => [
                'message' => 'Coś poszło nie tak. Plik nie został przesłany. aaa',
                'type' => 'error'
            ],
        ], Response::HTTP_BAD_REQUEST);
    }

    private function getUploadsDir()
    {
        return $this->getParameter('uploads_dir');
    }
}
