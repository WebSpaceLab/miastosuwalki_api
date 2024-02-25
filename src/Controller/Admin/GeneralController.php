<?php

namespace App\Controller\Admin;

use App\Controller\AbstractAPIController;
use App\Entity\General;
use App\Repository\GeneralRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/api/admin', name: 'app_admin_generals')]
class GeneralController extends AbstractAPIController
{
    #[Route('/generals', name: ':index', methods: ['GET'])]
    public function index(GeneralRepository $generalRepository): JsonResponse
    {
        $general = $generalRepository->findAll();
        return $this->response(['general' => $general]);
    }

    #[Route('/generals', name: ':create', methods: ['POST'])]
    public function create(GeneralRepository $generalRepository, ValidatorInterface $validator, Request $request): JsonResponse
    {
        $data = $request->toArray();


        $constraints = new Assert\Collection([
            'name' => [
                new NotBlank(),
                new Length(['min' => 2, 'minMessage' => 'Nazwa musi składać się z przynajmniej 2 liter.']),
            ],

            'description' => [
                new Length(['max' => 150, 'maxMessage' => 'Opis nie powinien przekraczać 150 liter.']),
            ],
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



        $existGeneral = $generalRepository->findAll();
        
        if($existGeneral) {
            return $this->json(['errors' => [
                'flash' => [
                    'type' => 'error',
                    'message' => 'Wystąpił nie spodziewany błąd'
                ]
            ]], Response::HTTP_EXPECTATION_FAILED);
        }

        $general = new General();
        $general->setName($data['name']);
        $general->setDescription($data['description']);

        $generalRepository->save($general, true);

        return $this->json(['flash' => [
            'type' => 'success',
            'message' => 'Utworzono nowy zapis'
        ]], Response::HTTP_CREATED);
    }

    #[Route('/generals', name: ':update', methods: ['PATCH'])]
    public function update(GeneralRepository $generalRepository, ValidatorInterface $validator, Request $request): JsonResponse
    {
        $data = $request->toArray();

        $constraints = new Assert\Collection([
            'name' => [
                new NotBlank(),
                new Length(['min' => 2, 'minMessage' => 'Nazwa musi składać się z przynajmniej 2 liter.']),
            ],

            'description' => [
                new Length(['max' => 150, 'maxMessage' => 'Opis nie powinien przekraczać 150 liter.']),
            ],
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



        $general = $generalRepository->find(1);
        
        if(!$general) {
            return $this->json(['errors' => [
                'flash' => [
                    'type' => 'error',
                    'message' => 'Wystąpił nie spodziewany błąd'
                ]
            ]], Response::HTTP_EXPECTATION_FAILED);
        }

        $general->setName($data['name']);
        $general->setDescription($data['description']);

        $generalRepository->save($general, true);

        return $this->json(['flash' => [
            'type' => 'success',
            'message' => 'Aktualizacja przeszła pomyślnie.'
        ]], Response::HTTP_CREATED);
    }
}
