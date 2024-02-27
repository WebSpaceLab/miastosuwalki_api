<?php

namespace App\Repository;

use App\Entity\Team;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;

/**
 * @extends ServiceEntityRepository<Team>
 *
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function getWithSearchQueryBuilder(?string $term, ?string $month, ?string $orderBy = 'createdAt', ?string $orderDir = 'DESC', ?string $status = 'false'): DoctrineQueryBuilder
    {
        $qb = $this->createQueryBuilder('team')
            ->andWhere('team.isDelete = false');

        if ($term) {
            $qb->andWhere('team.name LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        if($status) {
            $qb->andWhere('team.isActive LIKE :status')
                ->setParameter('status', $status);
        }

        if($month) {
            $from = Carbon::createFromFormat('d-m-Y', $month)->startOfMonth();
            $to = Carbon::createFromFormat('d-m-Y', $month)->endOfMonth();
    
            $qb->andWhere('team.createdAt BETWEEN :from AND :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }

        return $qb->orderBy('team.' . $orderBy , $orderDir);
    }

    public function getActiveTeam()
    {
        return $this->createQueryBuilder('team')
            ->andWhere('team.isDelete = false')
            ->andWhere('team.isActive = true')
            ->getQuery()
            ->getResult();
    }

    public function getActive()
    {
        return $this->createQueryBuilder('t')
            ->select('distinct t.isActive AS is_active')
            ->from('App:Team', 'team')
            ->where('t.isDelete = false')
            ->getQuery()
            ->getResult();
    }
    
    public function getActiveMonths()
    {
        return $this->createQueryBuilder('t')
            ->select('t.createdAt')
            ->distinct(true)
            ->from('App:Team', 'team')
            ->orderBy('t.createdAt', 'DESC')
            ->where('t.isDelete = false')
            ->getQuery()
            ->getResult();
    }

    public function save(Team $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Team $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Team[] Returns an array of Team objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Team
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
