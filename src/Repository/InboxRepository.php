<?php

namespace App\Repository;

use App\Entity\Inbox;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;
/**
 * @extends ServiceEntityRepository<Inbox>
 *
 * @method Inbox|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inbox|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inbox[]    findAll()
 * @method Inbox[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */ 
class InboxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inbox::class);
    }

    
    public function getWithSearchQueryBuilder(?string $term, ?string $month, ?string $orderBy = 'createdAt', ?string $orderDir = 'DESC', ?string $read = 'false'): DoctrineQueryBuilder
    {
        $qb = $this->createQueryBuilder('inbox')
            ->andWhere('inbox.isDelete = false');

        if ($term) {
            $qb->andWhere('inbox.subject LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        if($read) {
            $qb->andWhere('inbox.isRead LIKE :read')
                ->setParameter('read', $read);
        }

        if($month) {
            $from = Carbon::createFromFormat('d-m-Y', $month)->startOfMonth();
            $to = Carbon::createFromFormat('d-m-Y', $month)->endOfMonth();
    
            $qb->andWhere('inbox.createdAt BETWEEN :from AND :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }
        

        return $qb->orderBy('inbox.' . $orderBy , $orderDir);
    }

    public function getActiveIsRead()
    {
        return $this->createQueryBuilder('i')
            ->select('distinct i.isRead AS is_read')
            ->from('App:Inbox', 'inbox')
            ->where('i.isDelete = false')
            ->getQuery()
            ->getResult();
    }
    
    public function getActiveMonths()
    {
        return $this->createQueryBuilder('i')
            ->select('i.createdAt')
            ->distinct(true)
            ->from('App:Inbox', 'inbox')
            ->orderBy('i.createdAt', 'DESC')
            ->where('i.isDelete = false')
            ->getQuery()
            ->getResult();
    }

    public function save(Inbox $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Inbox $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
//    /**
//     * @return Inbox[] Returns an array of Inbox objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Inbox
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
