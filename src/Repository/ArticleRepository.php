<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Category;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function getWithSearchQueryBuilder(?string $term, ?string $orderBy = 'createdAt', ?string $orderDir = 'DESC', ?string $status = 'false', ?string $month): DoctrineQueryBuilder
    {
        $qb = $this->createQueryBuilder('article')
            ->andWhere('article.isDelete = false');

        if ($term) {
            $qb->andWhere('article.title LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        if($status) {
            $qb->andWhere('article.isPublished LIKE :status')
                ->setParameter('status', $status);
        }

        if($month) {
            $from = Carbon::createFromFormat('d-m-Y', $month)->startOfMonth();
            $to = Carbon::createFromFormat('d-m-Y', $month)->endOfMonth();
    
            $qb->andWhere('article.createdAt BETWEEN :from AND :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }

        return $qb->orderBy('article.' . $orderBy , $orderDir);
    }

    public function getWithSearchQueryBuilderOnlyPublished(?string $term, ?string $orderBy = 'createdAt', ?string $orderDir = 'DESC', ?string $month): DoctrineQueryBuilder
    {
        $qb = $this->createQueryBuilder('article')
            ->andWhere('article.isDelete = false')
            ->andWhere('article.isPublished = true');

        if ($term) {
            $qb->andWhere('article.title LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        if($month) {
            $from = Carbon::createFromFormat('d-m-Y', $month)->startOfMonth();
            $to = Carbon::createFromFormat('d-m-Y', $month)->endOfMonth();
    
            $qb->andWhere('article.createdAt BETWEEN :from AND :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }

        return $qb->orderBy('article.' . $orderBy , $orderDir);
    }

    public function getWithSearchQueryBuilderOnlyPublishedForCategory(Category $category, ?string $term, ?string $orderBy = 'createdAt', ?string $orderDir = 'DESC', ?string $month): DoctrineQueryBuilder
    {
        $qb = $this->createQueryBuilder('article')
            ->andWhere('article.isDelete = false')
            ->andWhere('article.isPublished = true')
            ->innerJoin('article.category', 'category')
            ->addSelect('category')
            ->andWhere('category = :category')
            ->setParameter('category', $category);

        if ($term) {
            $qb->andWhere('article.title LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        if($month) {
            $from = Carbon::createFromFormat('d-m-Y', $month)->startOfMonth();
            $to = Carbon::createFromFormat('d-m-Y', $month)->endOfMonth();
    
            $qb->andWhere('article.createdAt BETWEEN :from AND :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }

        return $qb->orderBy('article.' . $orderBy , $orderDir);
    }

    public function getPublishedArticle()
    {
        return $this->createQueryBuilder('article')
            ->andWhere('article.isDelete = false')
            ->andWhere('article.isPublished = true')
            ->getQuery()
            ->getResult();
    }

    public function getPublished()
    {
        return $this->createQueryBuilder('a')
            ->select('distinct a.isPublished AS is_published')
            ->from('App:Article', 'article')
            ->where('a.isDelete = false')
            ->getQuery()
            ->getResult();
    }
    
    public function getActiveMonths()
    {
        return $this->createQueryBuilder('a')
            ->select('a.createdAt')
            ->distinct(true)
            ->from('App:Article', 'article')
            ->orderBy('a.createdAt', 'DESC')
            ->where('a.isDelete = false')
            ->getQuery()
            ->getResult();
    }

    public function save(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findLatestInEachCategory()
    {
        $qb = $this->createQueryBuilder('a');

        $qb->select('a')
            ->andWhere('a.isDelete = false')
            ->andWhere('a.isPublished = true')
            ->innerJoin('a.category', 'c')
            ->groupBy('c.id')
            ->orderBy('c.createdAt', 'DESC')
            ;

        return $qb->getQuery()->getResult();
    }

    public function findLatestArticles(?int $count = 5)
    {
        $qb = $this->createQueryBuilder('a');

        $qb->select('a')
            ->andWhere('a.isDelete = false')
            ->andWhere('a.isPublished = true')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($count);

        return $qb->getQuery()->getResult();
    }

    public function findLatestArticlesByCategory($categoryName, ?int $count = 5)
    {
        $qb = $this->createQueryBuilder('a');

        $qb->select('a')
            ->andWhere('a.isDelete = false')
            ->andWhere('a.isPublished = true')
            ->innerJoin('a.category', 'c')
            ->andWhere('c.name = :categoryName')
            ->setParameter('categoryName', $categoryName)
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($count);

        return $qb->getQuery()->getResult();
    }


//    /**
//     * @return Article[] Returns an array of Article objects
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

//    public function findOneBySomeField($value): ?Article
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
