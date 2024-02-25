<?php

namespace App\Controller\Default;

use App\Controller\AbstractAPIController;
use App\Repository\PriceRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/price', name: 'app_price')]
class PriceListController extends AbstractAPIController
{
    #[Route('', name: ':index', methods: ['GET'])]
    public function index(PriceRepository $priceRepository): Response
    {
        $price =  $priceRepository->getActivePrice();

        return $this->response([
            'price' => $price,
        ], ['price:read']);
    }
}
