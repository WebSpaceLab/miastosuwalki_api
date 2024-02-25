<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Service\Attribute\Required;


abstract class AbstractAPIController extends AbstractController
{
    protected SerializerInterface $serializer;
    protected ValidatorInterface $validator;
    protected $flashBag;

    public function response( mixed $data, array $groups = [], int $status = 200, array $headers = []): JsonResponse
    {
        $context = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
                return $object->getId();
            },
            AbstractNormalizer::GROUPS => $groups,
        ];

        if($this->flashBag) {
            $flash = $this->flashBag;
        }

        return $this->json(
            [
                'data' => $data,
                'flash' => $flash ?? null
            ],
            $status,
            $headers,
            $context
        );
    }

    public function deserialize(
         mixed $data,
        string $type,
        string $format,
        array $context = []
    ): mixed
    {
        return $this->serializer->deserialize( 
            $data,
            $type,
            $format,
            $context
        );
    }

    public function redirectToFrontendRoute(string $routeName, int $status = 302)
    {
        $frontendUrl = $_ENV['FRONTEND_URL'];
        // $url = $this->generateUrl($routeName);
        $fullUrl = $frontendUrl . $routeName;
    
        return $this->redirect($fullUrl, $status);
    }

    protected function flash( mixed $message, string $type = 'success'): void
    {
        if($message) {
            $this->flashBag = [
                'type' => $type, 
                'message' => $message
            ];
        }
    }

    public function validate(mixed $value = null, mixed $data = null)
    {
        $constraints = new Assert\Collection($value);

        $violations = $this->validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $propertyPath = trim($violation->getPropertyPath(), '[\]');
                $errors[$propertyPath] = $violation->getMessage();
            }

            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST); 
        }

        return;
    }

    public function formatValidationErrors(ConstraintViolationListInterface $violations): array
    {
        $errors = [];
        foreach ($violations as $violation) {
            $propertyPath = trim($violation->getPropertyPath(), '[\]');
            $errors[$propertyPath] = $violation->getMessage();
        }

        return $errors;
    }

    

    #[Required]
    public function setServices(SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }
}
