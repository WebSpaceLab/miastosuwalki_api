<?php

namespace App\Controller\Editor;

use App\Controller\AbstractAPIController;
use App\Entity\Gallery;
use App\Repository\GalleryRepository;
use App\Repository\MediaRepository;
use App\Service\GalleriesHelper;
use App\Service\PaginationHelper;
use App\Service\QueryHelper;
use Doctrine\ORM\EntityManagerInterface;
use Proxies\__CG__\App\Entity\Media;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

// #[IsGranted('ROLE_EDITOR')]
#[Route('/api/editor/galleries', name: 'app_editor_galleries')]
class GalleryController extends AbstractAPIController
{
    public function __construct(
        private PaginationHelper $paginationHelper, 
        private QueryHelper $QueryHelper,
        private GalleriesHelper $galleryHelper
    ) {}
    
    #[Route('', name: ':index', methods: ['GET'])]
    public function index(GalleryRepository $galleryRepository, Request $request): JsonResponse
    {
        $query = $request->query->all();

        $queryBuilder = $galleryRepository->getWithSearchQueryBuilder(
            $query['term'], $query['orderBy'], $query['orderDir'], $query['status'], $query['month']
        );

        $pagination = $this->paginationHelper->paginate($queryBuilder, $query['page'], $query['per_page']);

        return $this->response([
            'galleries' => $pagination['data'],
            'pagination' => $pagination['pagination'],
            'queryParams' =>  $this->QueryHelper->params($request, ['term','status','month']),
            'status' => $this->galleryHelper->getPublished(),
            'months' => $this->galleryHelper->getMonths(),
        ], ['gallery:read']);
    }

    #[Route('', name: ':create', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, GalleryRepository $galleryRepository, MediaRepository $mediaRepository, ValidatorInterface $validator, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $gallery = $this->serializer->deserialize($request->getContent(), Gallery::class, 'json');
    
        $violations = $validator->validate($gallery);
    
        if (count($violations) > 0) {
            $errors = $this->formatValidationErrors($violations);
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }
               
        $gallery->setTitle($data['title'] ?? null);
        $gallery->setSlug($data['slug'] ?? $gallery->getTitle() . '-' . uniqid());
        $gallery->setDescription($data['description'] ?? null);
        $gallery->setAuthor($this->getUser());
        
        $gallery->setStartAt($data['startAt'] ?? null);
        $gallery->setEndAt($data['endAt'] ?? null);

        $gallery->setIsPublished($data['isPublished'] ?? false);
        $galleryRepository->save($gallery, true);

        if($data['photos']) {
            foreach ($data['photos'] as $medium) {
                $media = $mediaRepository->findOneBy(['id' => $medium['id']]);
                $gallery->addMedium($media);
                $entityManager->persist($media);
                $entityManager->persist($gallery);
                $entityManager->flush();
            }
        }

        $this->flash('Utworzenie nowej galerii zakończyło się sukcesem.');
        
        return $this->response([
            'gallery' => $gallery,
        ],['gallery:write'], Response::HTTP_CREATED);
    }

    #[Route('/{slug}', name: ':show', methods: ['GET'])]
    public function show(Gallery $gallery): JsonResponse
    {
        return $this->response([
            'gallery' => $gallery,
        ], ['gallery:read']);
    }

    #[Route('/{slug}', name: ':update', methods: ['PATCH'])]
    public function update(Gallery $gallery, GalleryRepository $galleryRepository, EntityManagerInterface $entityManager, MediaRepository $mediaRepository, ValidatorInterface $validator, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
       $gallery =  $this->deserialize($request->getContent(), Gallery::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $gallery,
        ]);

        $errors = $validator->validate($gallery);
        
        if(count($errors) > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }


        // $gallery->setAuthor($this->getUser());
        $gallery->setTitle($data['title']);
        $gallery->setSlug($data['slug']);
        $gallery->setDescription($data['description']);
        // $gallery->setStartAt($data['startAt']);
        // $gallery->setEndAt($data['endAt']);
        $gallery->setIsPublished($data['isPublished']);

        if($data['photos']) {
            foreach ($data['photos'] as $medium) {
                $media = $mediaRepository->findOneBy(['id' => $medium['id']]);
                $gallery->addMedium($media);
                $entityManager->persist($media);
                $entityManager->persist($gallery);
                $entityManager->flush();
            }
        }

        $galleryRepository->save($gallery, true);

        
        $this->flash('Zmiany zostały wprowadzone do galerii.');
        
        return $this->response([
            'gallery' => $gallery,
        ],['gallery:write'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: ':delete', methods: ['DELETE'])]
    public function delete(Gallery $gallery, GalleryRepository $galleryRepository): JsonResponse
    {
        $galleryRepository->remove($gallery, true);
        
        $this->flash('Galeria została usunięta.');
        
        return $this->response(null, ['gallery:write'], Response::HTTP_NO_CONTENT);
    }

    #[Route('/{slug}/photo/{mediaId}', name: ':restore', methods: ['DELETE'])]
    public function removePhotoFromGallery(Gallery $gallery, $mediaId, GalleryRepository $galleryRepository, MediaRepository $mediaRepository): JsonResponse
    {
        $medium = $mediaRepository->findOneBy(['id' => $mediaId]);

        $galleryRepository->removePhotoFromGallery($gallery, $medium, true);
        
        $this->flash('Zdjęcie zostało usunięte z galerii.');
        
        return $this->response(null, ['gallery:write'], Response::HTTP_NO_CONTENT);
    }

}
