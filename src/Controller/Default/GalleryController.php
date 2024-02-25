<?php

namespace App\Controller\Default;

use App\Controller\AbstractAPIController;
use App\Entity\Gallery;
use App\Repository\GalleryRepository;
use App\Service\GalleriesHelper;
use App\Service\PaginationHelper;
use App\Service\QueryHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/galleries', name: 'app_gallery')]
class GalleryController extends AbstractAPIController
{
    public function __construct(
        private PaginationHelper $paginationHelper, 
        private QueryHelper $QueryHelper,
        private GalleriesHelper $galleriesHelper
    ) {}

    #[Route('', name: ':index', methods: ['GET'])]
    public function index(Request $request, GalleryRepository $galleryRepository): JsonResponse
    {
        $query = $request->query->all();

        $queryBuilder = $galleryRepository->getWithSearchQueryBuilderOnlyPublished(
            $query['term'], $query['orderBy'], $query['orderDir'], $query['month']
        );

        $pagination = $this->paginationHelper->paginate($queryBuilder, $query['page'], $query['per_page']);

        return $this->response([
            'galleries' => $pagination['data'],
            'pagination' => $pagination['pagination'],
            'queryParams' =>  $this->QueryHelper->params($request, ['term','month']),
            'months' => $this->galleriesHelper->getMonths(),
        ], ['gallery:show']);
    }

    #[Route('/{slug}', name: ':show', methods: ['GET'])]
    public function show(Gallery $gallery): JsonResponse
    {
        return $this->response([            
            'gallery' => $gallery,
        ], ['gallery:show']);
    }
}
