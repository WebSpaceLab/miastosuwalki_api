<?php

namespace App\Controller\Admin;

use App\Controller\AbstractAPIController;
use App\Entity\Social;
use App\Repository\SocialRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/api/admin', name: 'app_admin_socials')]
class SocialController extends AbstractAPIController
{
    #[Route('/socials', name: ':index', methods: ['GET'])]
    public function index(SocialRepository $socialRepository): JsonResponse
    {
        $socials = $socialRepository->findAll();
        return $this->response(['socials' => $socials], ['social:read']);
    }

    #[Route('/socials', name: ':create', methods: ['POST'])]
    public function create(SocialRepository $socialRepository, ValidatorInterface $validator, Request $request): JsonResponse
    {
        $data = $request->toArray();

        $constraints = new Assert\Collection([
            'name' => [
                new NotBlank(['message' => 'To pole nie może być puste.']),
                new Length(['min' => 2, 'minMessage' => 'Nazwa musi składać się z przynajmniej 2 liter.']),
            ],

            'icon' => [],

            'path' => [],

            'is_active' => [],
        ]);

        
        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $propertyPath = trim($violation->getPropertyPath(), '[\]');
                $errors[$propertyPath] = $violation->getMessage();
            }

            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST); 
        }

        $social = new Social();
        $social->setName($data['name']);
        $social->setIcon($data['icon']);
        $social->setPath($data['path']);
        $social->setIsActive($data['is_active']);

        $socialRepository->save($social, true);

        return $this->json(['flash' => [
            'type' => 'success',
            'message' => 'Dodano social media'
        ]], Response::HTTP_CREATED);
    }

    #[Route('/socials/{id}', name: ':update', methods: ['PATCH'])]
    public function update(Social $social, SocialRepository $socialRepository, ValidatorInterface $validator, Request $request): JsonResponse
    {
        $data = $request->toArray();

        $constraints = new Assert\Collection([
            'name' => [
                new Length(['min' => 2, 'minMessage' => 'Nazwa musi składać się z przynajmniej 2 liter.']),
            ],

            'icon' => [],

            'path' => [],

            'is_active' => [],
        ]);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $propertyPath = trim($violation->getPropertyPath(), '[\]');
                $errors[$propertyPath] = $violation->getMessage();
            }

            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST); 
        }

        $social->setName($data['name']);
        $social->setIcon($data['icon']);
        $social->setPath($data['path']);
        $social->setIsActive($data['is_active']);

        $socialRepository->save($social, true);

        return $this->json(['flash' => [
            'type' => 'success',
            'message' => 'Aktualizacja mediów przeszedła pomyślnie.'
        ]], Response::HTTP_CREATED);
    }

    #[Route('/meta-tags/{id}', name: ':delete', methods: ['DELETE'])]
    public function delete(Social $social, SocialRepository $socialRepository): JsonResponse
    {
        $socialRepository->remove($social, true);

        return $this->json(['flash' => [
            'type' => 'success',
            'message' => 'Media zostały usunięte.'
        ]], Response::HTTP_CREATED);
    }
}
