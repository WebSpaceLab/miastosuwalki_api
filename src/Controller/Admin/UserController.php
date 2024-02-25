<?php

namespace App\Controller\Admin;

use App\Controller\AbstractAPIController;
use App\Entity\User;
use App\Entity\VerificationToken;
use App\Repository\UserRepository;
use App\Service\PaginationHelper;
use App\Service\QueryHelper;
use App\Service\UploaderHelper;
use App\Service\UsersHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use App\Repository\VerificationTokenRepository;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

#[isGranted('ROLE_ADMIN')]
#[Route('/api/admin/users', name: 'app_admin_users')]
class UserController extends AbstractAPIController
{
    public function __construct(
        private PaginationHelper $paginationHelper, 
        private QueryHelper $QueryHelper,
        private UsersHelper $usersHelper,
        private VerificationTokenRepository $verificationTokenRepository,
        private MailerInterface $mailer
    ) {}

    #[Route('', name: ':index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository): JsonResponse
    {
        $query = $request->query->all();

        $queryBuilder = $userRepository->getWithSearchQueryBuilder(
            $query['term'], $query['orderBy'], $query['orderDir'], $query['month']
        );

        $pagination = $this->paginationHelper->paginate($queryBuilder, $query['page'], $query['per_page']);

        return $this->response([
            'users' => $pagination['data'],
            'pagination' => $pagination['pagination'],
            'queryParams' =>  $this->QueryHelper->params($request, ['term','month']),
            'months' => $this->usersHelper->getMonths(),
        ], ['user:all']);
    }

    #[Route('', name: ':create', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository): JsonResponse
    {
        $data = $request->toArray();


        $constraints = new Assert\Collection([
            'username' => [
                new NotBlank(),
                new Length(['min' => 2, 'minMessage' => 'Nazwa musi składać się z przynajmniej 2 liter.']),
            ],

            'email' => [
                new NotBlank(),
                new Email()
            ],
            'password' => [
                new NotBlank(),
                new Length(['min' => 8, 'minMessage' => 'Hasło musi składać się z przynajmniej 8 liter.']),
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

        
        $user = $userRepository->findOneBy(['username' => $data['username']]);

        if($user) {
            return $this->json(['errors' => [
                'username' => 'Urzytkownik o tej nzwie już istnieje'
            ]], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->findOneBy(['email' => $data['email']]);

        if($user) {
            if(!$user->getVerificationToken()) {
                return $this->json(['errors' => [
                    'email' => 'Użytkownik o podanym emilu już istnieje.Jak dotąd rejstarcja, nie została potwierdzona. Sprawdź maila.'
                ]], Response::HTTP_BAD_REQUEST);
            }

            return $this->json(['errors' => [
                'email' => 'Użytkownik o podanym emilu już istnieje.'
            ]], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setIsActiveAccount(false);

        $userRepository->save($user, true);

        // Wygenerowanie tokenu weryfikacyjnego
        $verificationToken = $this->generateVerificationToken($user);

        // Wysłanie maila na podany adres email
        $this->sendVerificationEmail($user->getEmail(), $verificationToken, $user);

        $this->flash('Użytkownik został dodany. Wysłano maila weryfikacyjnego.');

        return $this->response([
            'user' => $user
        ], ['user:all']);
    }

    #[Route('/{slug}', name: '.show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        return $this->response($user, ['user:all']);
    }

    #[Route('/{id}', name: ':update', methods: ['PATCH'])]
    public function update(User $user, UserRepository $userRepository,  ValidatorInterface $validator, Request $request): JsonResponse
    {
        $user = $this->deserialize($request->getContent(), User::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $user,
        ]);

        $errors = $validator->validate($user);
        
        if(count($errors) > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $userRepository->save($user, true);

        $this->flash('Kategoria została zaktualizowana');
        
        return $this->response([
            'user' => $user
        ], ['user:all']);
    }

    #[Route('/{id}', name: ':delete', methods: ['DELETE'])]
    public function delete(UserRepository $userRepository,  User $user): JsonResponse
    {
        $user->setIsDelete(true);
        $userRepository->save($user, true);

        return $this->json([
            'flash' => [
                'type' => 'success',
                'message' => 'Użytkownik została usunięta'
            ]
        ], Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/avatar-update', name: ':avatar-update', methods: ['POST'])]
    public function avatarUrlUpdate(User $user, Request $request, UserRepository $userRepository, UploaderHelper $uploaderHelper): JsonResponse
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

    private function generateVerificationToken(User $user): string
    {
        $token = bin2hex(random_bytes(32));

        // Zapisanie tokena do bazy danych w tabeli VerificationToken
        $verificationToken = new VerificationToken();
        $verificationToken->setToken($token);
        $verificationToken->setOwnedBy($user);
        $verificationToken->setIsVerified(false);

        $this->verificationTokenRepository->save($verificationToken, true);

        return $token;
    }

    private function sendVerificationEmail(string $email, string $verificationToken, User $user): void
    {

        /* TODO - Zrobienie maila ktory będzie miał w akceptacje regulaminu  */
        $url = $this->generateUrl('verify_email', ['token' => $verificationToken], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new TemplatedEmail())
            ->from(new Address('noreply@example.com', 'Miasto Suwałki'))
            ->to(new Address($email, $user->getUsername()))
            ->subject('Potwierdzenie adresu email')
            ->htmlTemplate('email/verification-email.html.twig')
            ->context([
                'user' => $user,
                'url' => $url
            ]);

        $this->mailer->send($email);
    }
}
