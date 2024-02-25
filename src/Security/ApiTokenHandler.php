<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\ApiTokenRepository;
use DateInterval;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class ApiTokenHandler implements AccessTokenHandlerInterface {
    private const ACCESS_TOKEN_PREFIX = 'atp_';
    private $apiTokenRepository;

    public function __construct(ApiTokenRepository $apiTokenRepository)
    {
        $this->apiTokenRepository = $apiTokenRepository;
    }

    public function createForUser(User $user) 
    {
        $SessionToken = session_create_id();
        $tokenLifetime = new DateInterval('PT1H');
        $accessToken = self::ACCESS_TOKEN_PREFIX . $SessionToken . bin2hex(random_bytes(64));

        $this->apiTokenRepository->setApiTokenWithExpiration($accessToken, $user, $tokenLifetime, true);

        return $accessToken;
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        $token = $this->apiTokenRepository->findOneBy(['token' => $accessToken]);

        if (!$token) {
            throw new BadCredentialsException();
        }

        if (!$token->isValid()) {
            throw new CustomUserMessageAuthenticationException('Token expired');
        }

        return new UserBadge($token->getOwnedBy()->getUserIdentifier());
    }
}
