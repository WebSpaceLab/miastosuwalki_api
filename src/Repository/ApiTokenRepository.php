<?php

namespace App\Repository;

use App\Entity\ApiToken;
use App\Entity\User;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApiToken>
 *
 * @method ApiToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApiToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApiToken[]    findAll()
 * @method ApiToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApiTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiToken::class);
    }

    public function setApiTokenWithExpiration(string $token, User $user, ?DateInterval $tokenLifetime = null, bool $flush = false): void
    {
        if ($user->getApiToken() && $user->isIsAgree() && $user->isActiveAccount() && $user->isIsDelete() === false) {
            $user->getApiToken()->setToken($token);
    
            if ($tokenLifetime !== null) {
                // Oblicz nową datę wygaśnięcia na podstawie bieżącego czasu i okresu ważności.
                $expiresAt = (new DateTimeImmutable())->add($tokenLifetime);
                $user->getApiToken()->setExpiresAt($expiresAt);
            }
        } else {
            $apiToken = new ApiToken();
            $apiToken->setToken($token);
    
            if ($tokenLifetime !== null) {
                $expiresAt = (new DateTimeImmutable())->add($tokenLifetime);
                $apiToken->setExpiresAt($expiresAt);
            }
    
            $apiToken->setOwnedBy($user);
    
            $this->getEntityManager()->persist($apiToken);
        }

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ApiToken $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ApiToken[] Returns an array of ApiToken objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ApiToken
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
