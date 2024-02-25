<?php

namespace App\Repository;

use App\Entity\Hero;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @extends ServiceEntityRepository<Hero>
 *
 * @method Hero|null find($id, $lockMode = null, $lockVersion = null)
 * @method Hero|null findOneBy(array $criteria, array $orderBy = null)
 * @method Hero[]    findAll()
 * @method Hero[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HeroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hero::class);
    }

    public function getWithSearchQueryBuilder(?string $term, ?string $orderBy = 'createdAt', ?string $orderDir = 'DESC', ?string $status = 'false', ?string $month): DoctrineQueryBuilder
    {
        $qb = $this->createQueryBuilder('hero')
            ->andWhere('hero.isDelete = false');

        if ($term) {
            $qb->andWhere('hero.name LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        if($status) {
            $qb->andWhere('hero.isActive LIKE :status')
                ->setParameter('status', $status);
        }

        if($month) {
            $from = Carbon::createFromFormat('d-m-Y', $month)->startOfMonth();
            $to = Carbon::createFromFormat('d-m-Y', $month)->endOfMonth();
    
            $qb->andWhere('hero.createdAt BETWEEN :from AND :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }

        return $qb->orderBy('hero.' . $orderBy , $orderDir);
    }

    public function getActiveHero()
    {
        return $this->createQueryBuilder('hero')
            ->andWhere('hero.isDelete = false')
            ->andWhere('hero.isActive = true')
            ->getQuery()
            ->getResult();
    }

    public function getActive()
    {
        return $this->createQueryBuilder('h')
            ->select('distinct h.isActive AS is_active')
            ->from('App:Hero', 'hero')
            ->where('h.isDelete = false')
            ->getQuery()
            ->getResult();
    }
    
    public function getActiveMonths()
    {
        return $this->createQueryBuilder('h')
            ->select('h.createdAt')
            ->distinct(true)
            ->from('App:Hero', 'hero')
            ->orderBy('h.createdAt', 'DESC')
            ->where('h.isDelete = false')
            ->getQuery()
            ->getResult();
    }

    public function save(Hero $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Hero $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
//    /**
//     * @return hero[] Returns an array of hero objects
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

//    public function findOneBySomeField($value): ?hero
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
