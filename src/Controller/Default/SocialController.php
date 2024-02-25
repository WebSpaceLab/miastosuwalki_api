<?php

namespace App\Controller\Default;

use App\Controller\AbstractAPIController;
use App\Repository\SocialRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/socials', name: 'app_socials')]
class SocialController extends AbstractAPIController
{
    #[Route('', name: ':index', methods: ['GET'])]
    public function index(SocialRepository $socialRepository): Response
    {
        $socials = $socialRepository->findAll();

        return $this->response(['socials' => $socials], ['social:read']);
    }
    
}
