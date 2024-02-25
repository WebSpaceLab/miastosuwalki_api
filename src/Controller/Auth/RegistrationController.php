<?php

namespace App\Controller\Auth;

use App\Controller\AbstractAPIController;
use App\Entity\User;
use App\Entity\VerificationToken;
use App\Repository\UserRepository;
use App\Repository\VerificationTokenRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractAPIController
{
    public function __construct(
        private VerificationTokenRepository $verificationTokenRepository,
        private MailerInterface $mailer
    ) {}

    #[Route('/auth/register', name: 'app_auth_register', methods: ['POST'])]
    public function register(Request $request, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository): Response
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
            'isAgree' => [
                new NotBlank(['message' => 'Wymagana jest zgoda.']),
            ]
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
            if(!$user->getVerificationToken()->isIsVerified()) {
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
        $user->setIsAgree($data['isAgree']);

        $userRepository->save($user, true);

        // Wygenerowanie tokenu weryfikacyjnego
        $verificationToken = $this->generateVerificationToken($user);

        // Wysłanie maila weryfikacyjnego
        $this->sendVerificationEmail($user->getEmail(), $verificationToken, $user);

        return $this->json(['message' => 'Rejestracja zakończona sukcesem. Sprawdź swoją skrzynkę mailową w celu weryfikacji.'], 201);
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

    #[Route('/verify/email', name: 'verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository): RedirectResponse
    {
        $token = $request->query->get('token');

        if ($token == '' | null) {
            return $this->redirectToFrontendRoute('/');
        }

        $verificationToken = $this->verificationTokenRepository->findOneBy(['token' =>  $token]);
        $verificationToken->setIsVerified(true);

        $this->verificationTokenRepository->save($verificationToken, true);

        $userId = $verificationToken->getOwnedBy()->getId();

        $user =  $userRepository->find($userId);
        $user->setIsActiveAccount(true);

        $userRepository->save($user, true);

        return $this->redirectToFrontendRoute('/?verified=true');
    }
}
