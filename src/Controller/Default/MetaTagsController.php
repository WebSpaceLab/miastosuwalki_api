<?php

namespace App\Controller\Default;

use App\Controller\AbstractAPIController;
use App\Repository\MetaTagsRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/meta-tags', name: 'app_meta_tags')]
class MetaTagsController extends AbstractAPIController
{
    #[Route('', name: ':index', methods: ['GET'])]
    public function index(MetaTagsRepository $metaTagsRepository): Response
    {
        $metaTags = $metaTagsRepository->findAll();

        return $this->response(['metaTags' => $metaTags], ['meta:read']);
    }
}
