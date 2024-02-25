<?php

namespace App\Controller\Default;

use App\Controller\AbstractAPIController;
use App\Repository\TeamRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/team', name: 'app_team')]
class TeamController extends AbstractAPIController
{
    #[Route('', name: ':index', methods: ['GET'])]
    public function index(TeamRepository $teamRepository): Response
    {
        $team =  $teamRepository->getActiveTeam();
        return $this->response([
            'team' => $team,
        ], ['team:read']);
    }
}
