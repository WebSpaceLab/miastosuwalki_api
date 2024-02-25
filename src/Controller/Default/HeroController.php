<?php

namespace App\Controller\Default;

use App\Controller\AbstractAPIController;
use App\Repository\HeroRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/hero', name: 'app_hero')]
class HeroController extends AbstractAPIController
{
    #[Route('', name: ':index', methods: ['GET'])]
    public function index(HeroRepository $heroRepository): Response
    {
        $hero =  $heroRepository->getActiveHero();
        return $this->response([
            'hero' => $hero,
        ], ['hero:read']);
    }
}
