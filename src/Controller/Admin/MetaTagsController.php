<?php

namespace App\Controller\Admin;

use App\Controller\AbstractAPIController;
use App\Entity\MetaTags;
use App\Repository\MetaTagsRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/api/admin', name: 'app_admin_meta_tags')]
class MetaTagsController extends AbstractAPIController
{
    #[Route('/meta-tags', name: ':index', methods: ['GET'])]
    public function index(MetaTagsRepository $metaTagsRepository): JsonResponse
    {
        $metaTags = $metaTagsRepository->findAll();
        return $this->response(['metaTags' => $metaTags]);
    }

    #[Route('/meta-tags', name: ':create', methods: ['POST'])]
    public function create(MetaTagsRepository $metaTagsRepository, ValidatorInterface $validator, Request $request): JsonResponse
    {
        $data = $request->toArray();

        $constraints = new Assert\Collection([
            'name' => [
                new Length(['min' => 2, 'minMessage' => 'Nazwa musi składać się z przynajmniej 2 liter.']),
            ],

            'content' => [
                new Length(['max' => 150, 'maxMessage' => 'Opis nie powinien przekraczać 150 liter.']),
            ],

            'charset' => [],

            'http_equiv' => [],
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

        $metaTags = new MetaTags();
        $metaTags->setName($data['name']);
        $metaTags->setContent($data['content']);
        $metaTags->setCharset($data['charset']);
        $metaTags->setHttpEquiv($data['http_equiv']);

        $metaTagsRepository->save($metaTags, true);

        return $this->json(['flash' => [
            'type' => 'success',
            'message' => 'Utworzono nowy meta tag'
        ]], Response::HTTP_CREATED);
    }

    #[Route('/meta-tags/{id}', name: ':update', methods: ['PATCH'])]
    public function update(MetaTags $metaTags, MetaTagsRepository $metaTagsRepository, ValidatorInterface $validator, Request $request): JsonResponse
    {
        $data = $request->toArray();

        $constraints = new Assert\Collection([
            'name' => [
                new Length(['min' => 2, 'minMessage' => 'Nazwa musi składać się z przynajmniej 2 liter.']),
            ],

            'content' => [
                new Length(['max' => 150, 'maxMessage' => 'Opis nie powinien przekraczać 150 liter.']),
            ],

            'charset' => [],

            'http_equiv' => [],
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

        $metaTags->setName($data['name']);
        $metaTags->setContent($data['content']);
        $metaTags->setCharset($data['charset']);
        $metaTags->setHttpEquiv($data['http_equiv']);

        $metaTagsRepository->save($metaTags, true);

        return $this->json(['flash' => [
            'type' => 'success',
            'message' => 'Aktualizacja meta tagu przeszedła pomyślnie.'
        ]], Response::HTTP_CREATED);
    }

    #[Route('/meta-tags/{id}', name: ':delete', methods: ['DELETE'])]
    public function delete(MetaTags $metaTags, MetaTagsRepository $metaTagsRepository): JsonResponse
    {
        $metaTagsRepository->remove($metaTags, true);

        return $this->json(['flash' => [
            'type' => 'success',
            'message' => 'Meta tag został usunięty.'
        ]], Response::HTTP_CREATED);
    }
}
