<?php

namespace App\Repository;

use App\Entity\Category;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function getWithSearchQueryBuilder(?string $term, ?string $orderBy = 'createdAt', ?string $orderDir = 'DESC', ?string $status = 'false', ?string $month): DoctrineQueryBuilder
    {
        $qb = $this->createQueryBuilder('category')
            ->andWhere('category.isDelete = false');

        if ($term) {
            $qb->andWhere('category.title LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        if($status) {
            $qb->andWhere('category.isPublished LIKE :status')
                ->setParameter('status', $status);
        }

        if($month) {
            $from = Carbon::createFromFormat('d-m-Y', $month)->startOfMonth();
            $to = Carbon::createFromFormat('d-m-Y', $month)->endOfMonth();
    
            $qb->andWhere('category.createdAt BETWEEN :from AND :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }

        return $qb->orderBy('category.' . $orderBy , $orderDir);
    }

    public function getActiveCategories()
    {
        return $this->createQueryBuilder('category')
            ->andWhere('category.isDelete = false')
            ->andWhere('category.isActive = true')
            ->getQuery()
            ->getResult();
    }

    public function getActive()
    {
        return $this->createQueryBuilder('c')
            ->select('distinct c.isActive AS is_active')
            ->from('App:Category', 'category')
            ->where('c.isDelete = false')
            ->getQuery()
            ->getResult();
    }

    
    public function getActiveMonths()
    {
        return $this->createQueryBuilder('c')
            ->select('c.createdAt')
            ->distinct(true)
            ->from('App:Category', 'category')
            ->orderBy('c.createdAt', 'DESC')
            ->where('c.isDelete = false')
            ->getQuery()
            ->getResult();
    }

    public function save(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Category[] Returns an array of Category objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Category
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
