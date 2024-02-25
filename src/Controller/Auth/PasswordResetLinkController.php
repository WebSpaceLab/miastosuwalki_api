<?php

namespace App\Controller\Auth;

use App\Controller\AbstractAPIController;
use App\Entity\ResetPasswordToken;
use App\Entity\User;
use App\Repository\ResetPasswordTokenRepository;
use App\Repository\UserRepository;
use DateInterval;
use DateTimeImmutable;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\JsonResponse;
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

class PasswordResetLinkController extends AbstractAPIController
{
    public function __construct(
        private ResetPasswordTokenRepository $resetPasswordTokenRepository,
        private MailerInterface $mailer
    ) {}


    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgetPassword(Request $request, ValidatorInterface $validator, UserRepository $userRepository): JsonResponse
    {
        $data = $request->toArray();

        $constraints = new Assert\Collection([
            'email' => [
                new NotBlank(['message' => 'To pole nie powinno być puste.']),
                new Email(['message' => 'Ta wartość nie jest prawidłowym adresem e-mail.']),
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

        $user = $userRepository->findOneBy(['email' => $data['email']]);
        
        if (!$user) {
            return new JsonResponse(['errors' => [
                'email' => 'Użytkownik o podanym adresie email nie istnieje.'
            ]], Response::HTTP_NOT_FOUND);
        }
        
        $resetPasswordToken = $this->generateResetPasswordToken($user);
        $this->sendResetPasswordEmail($data['email'], $resetPasswordToken, $user);

        return $this->response(['message' => 'Mail z linkiem do resetu hasła został wysłany. Proszę sprwdź konto mailowe.']);
    }

    private function generateResetPasswordToken(User $user): string
    {
        $token = bin2hex(random_bytes(32));
        $tokenLifetime = new DateInterval('PT1H');

        $resetPasswordToken= new ResetPasswordToken();
        $resetPasswordToken->setToken($token);
        $resetPasswordToken->setOwnedBy($user);
        $resetPasswordToken->setIsVerified(false);

        if ($tokenLifetime !== null) {
            $expiresAt = (new DateTimeImmutable())->add($tokenLifetime);
            $resetPasswordToken->setExpiresAt($expiresAt);
        }
        $this->resetPasswordTokenRepository->save($resetPasswordToken, true);

        return $token;
    }

    private function sendResetPasswordEmail(string $email, string $resetPasswordToken, User $user): void
    {
        $url = $this->generateUrl('verify_email:forget_password', ['token' => $resetPasswordToken], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new TemplatedEmail())
            ->from(new Address('noreply@example.com', 'Miasto Suwałki'))
            ->to(new Address($email, $user->getUsername()))
            ->subject('Potwierdzenie adresu email')
            ->htmlTemplate('email/forget-password-verification-email.html.twig')
            ->context([
                'user' => $user,
                'url' => $url
            ]);

        $this->mailer->send($email);
    }

    
    #[Route('/forgot-password/verify-email/', name: 'verify_email:forget_password')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository): RedirectResponse
    {
        $token = $request->query->get('token');

        if ($token == '' | null) {
            return $this->redirectToFrontendRoute('/');
        }

        $resetPasswordToken = $this->resetPasswordTokenRepository->findOneBy(['token' =>  $token]);
        $resetPasswordToken->setIsVerified(true);

        $this->resetPasswordTokenRepository->save($resetPasswordToken, true);

        return $this->redirectToFrontendRoute('/password-reset/' . $resetPasswordToken->getToken());
    }

    #[Route('/reset-password', name: 'reset_password', methods: ['POST'])]
    public function resetPassword( Request $request, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher, ResetPasswordTokenRepository $resetPasswordTokenRepository,UserRepository $userRepository): JsonResponse
    {
        // Odczytanie danych wejściowych (nowe hasło i potwierdzenie hasła)
        $data = $request->toArray();

        $token = $resetPasswordTokenRepository->findOneBy([
            'token' => $data['token']
        ]);

        // Weryfikacja tokenu oraz proces resetu hasła
        if (!$token) {
            return new JsonResponse(['errors' => ['token' => 'Nieprawidłowy token resetu hasła.']], Response::HTTP_BAD_REQUEST);
        }

        // Weryfikacja czy token jest aktualny
        if (!$token->isValid()) {
            return new JsonResponse(['errors' => ['token' => 'Token weryfikacji nie jest już aktualny.']], Response::HTTP_BAD_REQUEST);
        }

        /* Weryfikacja hasła */
        $constraints = new Assert\Collection([
            'token' => [
                new NotBlank(),
                new Length(['min' => 63, 'max' => 65, 'minMessage' => 'Nie prawidłowy token.', 'maxMessage' => 'Nie prawidłowy token.' ]),
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

        $user = $token->getOwnedBy();
        // Zamian hała na nowe
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $userRepository->save($user, true);
        
        // Przypisanie dla isPasswordHasBeenReset(true) oznacza, że token został wykorzystany do zmiany hasła
        $token->setIsPasswordHasBeenReset(true);
        $resetPasswordTokenRepository->save($token, true);

        return $this->json([], Response::HTTP_NO_CONTENT);
    }
}
