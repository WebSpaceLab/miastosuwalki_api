<?php

namespace App\Controller\Admin;

use App\Controller\AbstractAPIController;
use App\Entity\Inbox;
use App\Repository\InboxRepository;
use App\Service\InboxHelper;
use App\Service\PaginationHelper;
use App\Service\QueryHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/api/admin/inbox', name: 'app_admin_inbox')]
class InboxController extends AbstractAPIController
{
    public function __construct(
        private PaginationHelper $paginationHelper, 
        private QueryHelper $QueryHelper,
        private InboxHelper $inboxHelper
    ) {}

    #[Route('', name: ':list', methods: ['GET'])]
    public function list(Request $request, InboxRepository $inboxRepository): JsonResponse
    {
        $query = $request->query->all();

        $queryBuilder = $inboxRepository->getWithSearchQueryBuilder(
            $query['term'], $query['orderBy'], $query['orderDir'], $query['read'], $query['month']
        );

        $pagination = $this->paginationHelper->paginate($queryBuilder, $query['page'], $query['per_page']);

        return $this->response([
            'inbox' => $pagination['data'],
            'pagination' => $pagination['pagination'],
            'queryParams' =>  $this->QueryHelper->params($request, ['term','read','month']),
            'read' => $this->inboxHelper->getRead(),
            'months' => $this->inboxHelper->getMonths(),
        ], ['admin:inbox:read']);
    }

    #[Route('/{id}', name: ':update', methods: ['PATCH'])]
    public function update(Inbox $inbox, InboxRepository $inboxRepository): JsonResponse
    {
        $inbox->setIsRead(true);

        $inboxRepository->save($inbox, true);

        return $this->json([
            'mail' => $inbox,
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: ':delete', methods: ['DELETE'])]
    public function remove(Inbox $inbox, InboxRepository $inboxRepository): JsonResponse
    {
        if (!$inbox) {
            return $this->json([
                'flash' => [
                    'type' => 'error',
                    'message' => 'Wiadomość nie istnieje.',
                ],
            ]);
        }

        $inbox->setIsDelete(true);

        $inboxRepository->save($inbox, true);

        return $this->json([
            'flash' => [
                'type' => 'success',
                'message' => 'Wiadomość została usunięta.',
            ],
        ], Response::HTTP_OK);
    }
}
