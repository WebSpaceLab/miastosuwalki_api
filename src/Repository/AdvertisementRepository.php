<?php

namespace App\Repository;

use App\Entity\Advertisement;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;

/**
 * @extends ServiceEntityRepository<Advertisement>
 *
 * @method Advertisement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Advertisement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Advertisement[]    findAll()
 * @method Advertisement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdvertisementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advertisement::class);
    }

    public function getWithSearchQueryBuilder(?string $term, ?string $orderBy = 'createdAt', ?string $orderDir = 'DESC', ?string $status = 'false', ?string $month): DoctrineQueryBuilder
    {
        $qb = $this->createQueryBuilder('advertisement');

        if ($term) {
            $qb->andWhere('advertisement.name LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        if($status) {
            $qb->andWhere('advertisement.isActive LIKE :status')
                ->setParameter('status', $status);
        }

        if($month) {
            $from = Carbon::createFromFormat('d-m-Y', $month)->startOfMonth();
            $to = Carbon::createFromFormat('d-m-Y', $month)->endOfMonth();
    
            $qb->andWhere('advertisement.createdAt BETWEEN :from AND :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }

        return $qb->orderBy('advertisement.' . $orderBy , $orderDir);
    }

    public function getActive()
    {
        return $this->createQueryBuilder('a')
            ->select('distinct a.isActive AS is_active')
            ->from('App:Advertisement', 'advertisement')
            ->getQuery()
            ->getResult();
    }
    
    public function getActiveMonths()
    {
        return $this->createQueryBuilder('a')
            ->select('a.createdAt')
            ->distinct(true)
            ->from('App:Advertisement', 'advertisement')
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function save(Advertisement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Advertisement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Advertisement[] Returns an array of Advertisement objects
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

//    public function findOneBySomeField($value): ?Advertisement
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
