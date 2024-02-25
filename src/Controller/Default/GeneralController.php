<?php

namespace App\Controller\Default;

use App\Controller\AbstractAPIController;
use App\Repository\GeneralRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class GeneralController extends AbstractAPIController
{
    #[Route('/api/generals', name: 'app_generals', methods: ['GET'])]
    public function index(GeneralRepository $generalRepository): JsonResponse
    {
        $general = $generalRepository->findAll();
        return $this->response(['general' => $general]);
    }
}
