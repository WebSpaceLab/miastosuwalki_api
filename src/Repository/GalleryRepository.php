<?php

namespace App\Repository;

use App\Entity\Gallery;
use App\Entity\Media;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Gallery>
 *
 * @method Gallery|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gallery|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gallery[]    findAll()
 * @method Gallery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GalleryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gallery::class);
    }

    public function getWithSearchQueryBuilder(?string $term, ?string $month, ?string $orderBy = 'createdAt', ?string $orderDir = 'DESC', ?string $status = 'false'): DoctrineQueryBuilder
    {
        $qb = $this->createQueryBuilder('gallery')
            ->andWhere('gallery.isDelete = false');

        if ($term) {
            $qb->andWhere('gallery.title LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        if($status) {
            $qb->andWhere('gallery.isPublished LIKE :status')
                ->setParameter('status', $status);
        }

        if($month) {
            $from = Carbon::createFromFormat('d-m-Y', $month)->startOfMonth();
            $to = Carbon::createFromFormat('d-m-Y', $month)->endOfMonth();
    
            $qb->andWhere('gallery.createdAt BETWEEN :from AND :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }

        return $qb->orderBy('gallery.' . $orderBy , $orderDir);
    }

    public function getWithSearchQueryBuilderOnlyPublished(?string $term, ?string $month, ?string $orderBy = 'createdAt', ?string $orderDir = 'DESC'): DoctrineQueryBuilder
    {
        $qb = $this->createQueryBuilder('gallery')
            ->andWhere('gallery.isDelete = false')
            ->andWhere('gallery.isPublished = true');

        if ($term) {
            $qb->andWhere('gallery.title LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        if($month) {
            $from = Carbon::createFromFormat('d-m-Y', $month)->startOfMonth();
            $to = Carbon::createFromFormat('d-m-Y', $month)->endOfMonth();
    
            $qb->andWhere('gallery.createdAt BETWEEN :from AND :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }

        return $qb->orderBy('gallery.' . $orderBy , $orderDir);
    }

    public function getPublishedGalleries()
    {
        return $this->createQueryBuilder('gallery')
            ->andWhere('gallery.isDelete = false')
            ->andWhere('gallery.isPublished = true')
            ->getQuery()
            ->getResult();
    }

    public function getPublished()
    {
        return $this->createQueryBuilder('g')
            ->select('distinct g.isPublished AS is_published')
            ->from('App:Gallery', 'gallery')
            ->where('g.isDelete = false')
            ->getQuery()
            ->getResult();
    }
    
    public function getActiveMonths()
    {
        return $this->createQueryBuilder('g')
            ->select('g.createdAt')
            ->distinct(true)
            ->from('App:Gallery', 'gallery')
            ->orderBy('g.createdAt', 'DESC')
            ->where('g.isDelete = false')
            ->getQuery()
            ->getResult();
    }

    public function save(Gallery $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Gallery $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function removePhotoFromGallery(Gallery $gallery, Media $medium, bool $flush = false): void
    {

        $gallery->removeMedium($medium);
        $this->getEntityManager()->persist($gallery);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Gallery[] Returns an array of Gallery objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Gallery
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
