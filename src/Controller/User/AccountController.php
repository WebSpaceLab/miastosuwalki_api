<?php

namespace App\Controller\User;

use App\Controller\AbstractAPIController;
use App\Entity\Media;
use App\Entity\User;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;
use App\Service\UploaderHelper;
use Intervention\Image\File;
use Intervention\Image\ImageManagerStatic;
use Janwebdev\ImageBundle\Image;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/user', name: 'app_user_account', methods: ["GET"])]
class AccountController extends AbstractAPIController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: ':show')]
    public function show(#[CurrentUser()] User $user = null): JsonResponse
    {
        return $this->response($user, ['profile:read']);
    }
    
    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: ':update', methods: ['PATCH'])]
    public function update(User $user, UserRepository $userRepository, ValidatorInterface $validator, Request $request): JsonResponse
    {
        $user = $this->deserialize($request->getContent(), User::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $user,
            'groups' => ['profile:write']
        ]);

        $errors = $validator->validate($user);
        
        if(count($errors) > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $userRepository->save($user, true);

        return $this->json([
            'flash' => [
                'type' => 'success',
                'message' => 'Dane użytkownika zostały zaktualizowane.'
            ],
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/update-password', name: ':update-password', methods: ['PATCH'])]
    public function updatePassword(#[CurrentUser()] User $user = null, Request $request, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository): JsonResponse
    {
        $data = $request->toArray();
        
        $constraints = new Assert\Collection([
            'current_password' => [
                new NotBlank(),
                new Length(['min' => 8]),
            ],
            'password' => [
                new NotBlank(),
                new Length(['min' => 8]),
            ],
            'password_confirmation' => [
                new NotBlank(),
                new Length(['min' => 8]),
                new EqualTo(['value' => $data['password'], 'message' => 'Hasła nie są zgodne.']),
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

        if (!$passwordHasher->isPasswordValid($user, $data['current_password'])) {
            return $this->json(['errors' => [
                'current_password' =>  'Nieprawidłowe hasło'
            ]], Response::HTTP_BAD_REQUEST);
        }
    
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $data['password']
        );

        $user->setPassword($hashedPassword);
        $userRepository->save($user, true);
        
        return $this->json([
            'flash' => [
                'type' => 'success',
                'massage' => 'Zmiana hasła powiodła się'
            ],
        ]);
    }

    #[Route('/{id}/avatar-update', name: ':avatar-update', methods: ['POST'])]
    public function avatarUrlUpdate(#[CurrentUser()] User $user, Request $request, UserRepository $userRepository, UploaderHelper $uploaderHelper): JsonResponse
    {
        $file = $request->files->get('image');
        $this->validate([
            'image' => [
                new NotBlank(),
            ],
        ], $file);
        
        

        if($file) {
            $filename = $uploaderHelper->createdFileName($file);
            $uploadDir = $this->getParameter('uploads_dir') .'/profile/'. $user->getId();
            
            // $image = new Image($imageManager, ['driver' => 'imagick']);
            // $img = $image->create($uploadDir);
            
            // $croppedImage = $img->crop(
            //     $request->request->get('width'),
            //     $request->request->get('height'),
            //     $request->request->get('left'),
            //     $request->request->get('top'),
            // );
            
            // $croppedImage->save($uploadDir);

            $filename = $uploaderHelper->uploadImage($file, $uploadDir);
            
            if(!$filename) {
                return $this->json([
                    'flash' => [
                        'message' => 'Coś poszło nie tak. Plik nie został przesłany.',
                        'type' => 'error'
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $user->setAvatarUrl('/uploads/profile/'. $user->getId(). '/' . $filename);
            $userRepository->save($user, true);

            $this->flash('Zdjęcie profilowe zostało zaktualizowane.');

            return $this->response(['user' => $user], ['user:read']);
        }

        return $this->json([
            'flash' => [
                'message' => 'Coś poszło nie tak. Plik nie został przesłany.',
                'type' => 'error'
            ],
        ], Response::HTTP_BAD_REQUEST);
    }
}
