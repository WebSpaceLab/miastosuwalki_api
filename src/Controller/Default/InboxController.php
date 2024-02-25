<?php

namespace App\Controller\Default;

use App\Controller\AbstractAPIController;
use App\Entity\Inbox;
use App\Repository\InboxRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/inbox', name: 'app_inbox')]
class InboxController extends AbstractAPIController
{
    #[Route('/', name: ':create', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator, InboxRepository $inboxRepository): JsonResponse
    {
        $data = $request->toArray();

        $constraints = new Assert\Collection([
            'subject' => [
                new NotBlank(),
                new Length(['min' => 2, 'minMessage' => 'Temat jest wymagany.']),
            ],
            'sender' => [
                new NotBlank(),
                new Length(['min' => 5, 'minMessage' => 'Podaj imię i nazwisko.']),
            ],
            'email' => [
                new NotBlank(['message' => 'Podaj poprawny adres email.']),
                new Email(['message' => 'Podaj poprawny adres email.']),
            ],
            'phone' => [
                new NotBlank(),
                new Length(['min' => 6, 'minMessage' => 'Podaj numer telefonu.']),
            ],
            'content' => [
                new NotBlank(['message' => 'Treść jest wymagana.']),
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

        $inbox = new Inbox();
        $inbox->setSubject($data['subject']);
        $inbox->setSender($data['sender']);
        $inbox->setEmail($data['email']);
        $inbox->setPhone($data['phone']);
        $inbox->setContent($data['content']);
        
        $inboxRepository->save($inbox, true);

        return $this->json([
            'flash' => [
                'type' => 'success',
                'message' => 'Wiadomość została wysłana.',
            ],
        ], Response::HTTP_CREATED);
    }
}
