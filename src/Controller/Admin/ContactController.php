<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[IsGranted('ROLE_ADMIN')]
#[Route('/api/admin', name: 'app_admin_contact')]
class ContactController extends AbstractController
{
    #[Route('/contacts', name: ':create', methods: ['POST'])]
    public function create(ContactRepository $contactRepository, ValidatorInterface $validator, Request $request): JsonResponse
    {
        $data = $request->toArray();

        $constraints = new Assert\Collection([
            'name' => [
                new NotBlank(),
                new Length(['min' => 2, 'minMessage' => 'Nazwa musi składać się z przynajmniej 2 liter.']),
            ],

            'description' => [
                new Length(['max' => 255, 'maxMessage' => 'Opis nie powinien przekraczać 255 liter.']),
            ],

            'address' => [],

            'openingHours' => [],

            'phone' => [],

            'map' => [],
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

        $existContact = $contactRepository->findAll();
        
        if($existContact) {
            return $this->json(['errors' => [
                'flash' => [
                    'type' => 'error',
                    'message' => 'Wystąpił nie spodziewany błąd'
                ]
            ]], Response::HTTP_EXPECTATION_FAILED);
        }

        $contact = new Contact();
        $contact->setName($data['name']);
        $contact->setDescription($data['description']);
        $contact->setAddress($data['address']);
        $contact->setOpeningHours($data['openingHours']);
        $contact->setPhone($data['phone']);
        $contact->setMap($data['map']);

        $contactRepository->save($contact, true);

        return $this->json(['flash' => [
            'type' => 'success',
            'message' => 'Utworzono kontakt'
        ]], Response::HTTP_CREATED);
    }

    
    #[Route('/contacts', name: ':update', methods: ['PATCH'])]
    public function update(ContactRepository $contactRepository, ValidatorInterface $validator, Request $request): JsonResponse
    {
        $data = $request->toArray();

        $constraints = new Assert\Collection([
            'name' => [
                new NotBlank(),
                new Length(['min' => 2, 'minMessage' => 'Nazwa musi składać się z przynajmniej 2 liter.']),
            ],

            'description' => [
                new Length(['max' => 255, 'maxMessage' => 'Opis nie powinien przekraczać 255 liter.']),
            ],

            'address' => [],

            'openingHours' => [],

            'phone' => [],

            'map' => [],
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



        $contact = $contactRepository->find(1);
        
        if(!$contact) {
            return $this->json(['errors' => [
                'flash' => [
                    'type' => 'error',
                    'message' => 'Wystąpił nie spodziewany błąd'
                ]
            ]], Response::HTTP_EXPECTATION_FAILED);
        }

        $contact->setName($data['name']);
        $contact->setDescription($data['description']);
        $contact->setAddress($data['address']);
        $contact->setOpeningHours($data['openingHours']);
        $contact->setPhone($data['phone']);
        $contact->setMap($data['map']);

        $contactRepository->save($contact, true);

        return $this->json(['flash' => [
            'type' => 'success',
            'message' => 'Aktualizacja przeszła pomyślnie.'
        ]], Response::HTTP_CREATED);
    }

}
