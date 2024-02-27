<?php

namespace App\Repository;

use App\Entity\Price;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;
/**
 * @extends ServiceEntityRepository<Price>
 *
 * @method Price|null find($id, $lockMode = null, $lockVersion = null)
 * @method Price|null findOneBy(array $criteria, array $orderBy = null)
 * @method Price[]    findAll()
 * @method Price[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Price::class);
    }

    public function getWithSearchQueryBuilder(?string $term, ?string $month, ?string $orderBy = 'createdAt', ?string $orderDir = 'ASC', ?string $status = 'false'): DoctrineQueryBuilder
    {
        $qb = $this->createQueryBuilder('price')
            ->andWhere('price.isDelete = false');

        if ($term) {
            $qb->andWhere('price.title LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        if($status) {
            $qb->andWhere('price.isActive LIKE :status')
                ->setParameter('status', $status);
        }

        if($month) {
            $from = Carbon::createFromFormat('d-m-Y', $month)->startOfMonth();
            $to = Carbon::createFromFormat('d-m-Y', $month)->endOfMonth();
    
            $qb->andWhere('price.createdAt BETWEEN :from AND :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }

        return $qb->orderBy('price.' . $orderBy , $orderDir);
    }

    public function getActivePrice()
    {
        return $this->createQueryBuilder('price')
            ->andWhere('price.isDelete = false')
            ->andWhere('price.isActive = true')
            ->innerJoin('price.packages', 'package')
            ->addSelect('package')
            ->andWhere('package.isDelete = false')
            ->andWhere('package.isActive = true')
            ->getQuery()
            ->getResult();
    }

    public function getActive()
    {
        return $this->createQueryBuilder('p')
            ->select('distinct p.isActive AS is_active')
            ->from('App:Price', 'price')
            ->where('p.isDelete = false')
            ->getQuery()
            ->getResult();
    }
    
    public function getActiveMonths()
    {
        return $this->createQueryBuilder('p')
            ->select('p.createdAt')
            ->distinct(true)
            ->from('App:Price', 'price')
            ->orderBy('p.createdAt', 'DESC')
            ->where('p.isDelete = false')
            ->getQuery()
            ->getResult();
    }

    public function save(Price $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Price $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Price[] Returns an array of Price objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Price
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
