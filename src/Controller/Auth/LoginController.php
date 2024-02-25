<?php

namespace App\Controller\Auth;

use App\Controller\AbstractAPIController;
use App\Entity\User;
use App\Security\ApiTokenHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class LoginController extends AbstractAPIController
{
    #[Route('/auth/login', name: 'app_auth_login', methods: ['POST'])]
    public function login(#[CurrentUser()] User $user = null, ApiTokenHandler $apiTokenHandler): JsonResponse
    {
        if(!$user) {
            return $this->json([
                'error' => 'Invalid login request: check that the Content-Type header is "application/json".',
            ], Response::HTTP_UNAUTHORIZED);
        }

        
        if(!$user->getVerificationToken()) {
            return $this->json([
                'error' => 'The account is not verified. Please check your email.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if(!$user->getVerificationToken()->isIsVerified()) {
            return $this->json([
                'error' => 'The account is not verified. Please check your email.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if(!$user->isActiveAccount()) {
            return $this->json([
                'error' => 'The account is not active. ',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if(!$user->isIsAgree()) {
            return $this->json([
                'error' => 'The account is not agree.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if($user->isIsDelete()) {
            return $this->json([
                'error' => 'The account is deleted. Please contact the administrator.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $apiToken = $apiTokenHandler->createForUser($user);

        $this->flash('Logowanie przebiegło pomyślnie.');

        return $this->response([
            'apiToken' => $apiToken,
            'iri' => $user->getIriFromResource(),
        ]);
    }

    #[Route('/auth/logout', name: 'app_auth_logout', methods: ['POST'])]
    public function logout(): void
    {
        throw new \Exception('Logout failed?'); 
    }

    #[Route('/auth/logout', name: 'app_auth_logout_redirected')]
    public function logoutRedirected(): JsonResponse
    {
        $this->flash('Wylogowano pomyślnie.');

        return $this->response([]);
    }
}
