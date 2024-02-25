<?php

namespace App\Repository;

use App\Entity\Media;
use App\Service\MediaHelper;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;

/**
 * @extends ServiceEntityRepository<Media>
 *
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    public function getWithSearchQueryBuilder(?string $term, ?string $orderBy = 'createdAt', ?string $orderDir = 'DESC', ?string $fileType = 'image', ?string $month): DoctrineQueryBuilder
    {
        $qb = $this->createQueryBuilder('media');

        if ($term) {
            $qb->andWhere('media.name LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        if($fileType) {
            $qb->andWhere('media.mimeType LIKE :mime_types')
                ->setParameter('mime_types', $fileType);
        }

        if($month) {
            $from = Carbon::createFromFormat('d-m-Y', $month)->startOfMonth();
            $to = Carbon::createFromFormat('d-m-Y', $month)->endOfMonth();
    
            $qb->andWhere('media.createdAt BETWEEN :from AND :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }
        

        return $qb->orderBy('media.' . $orderBy , $orderDir);
    }

    public function getWithSearchQueryBuilderForUser(?string $term, ?string $orderBy = 'createdAt', ?string $orderDir = 'DESC', ?string $fileType, ?string $month, ?int $userId): DoctrineQueryBuilder
    {
        $qb = $this->createQueryBuilder('media')
            ->andWhere('media.isDelete = false')
            ->innerJoin('media.author', 'user')
            ->addSelect('user')
            ->andWhere('user.id = :userId')
            ->setParameter('userId', $userId);

        if ($term) {
            $qb->andWhere('media.name LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        if($fileType) {
            $qb->andWhere('media.mimeType IN :mimeType')
                ->setParameter('mimeType', MediaHelper::getMimes($fileType));
        }

        if($month) {
            $from = Carbon::createFromFormat('d-m-Y', $month)->startOfMonth();
            $to = Carbon::createFromFormat('d-m-Y', $month)->endOfMonth();
    
            $qb->andWhere('media.createdAt BETWEEN :from AND :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }
        

        return $qb->orderBy('media.' . $orderBy , $orderDir);
    }

    public function getActiveMimeTypes()
    {
        return $this->createQueryBuilder('m')
            ->select('distinct m.mimeType AS mime_type')
            ->from('App:Media', 'media')
            ->where('m.isDelete = false')
            ->getQuery()
            ->getResult();
    }

    public function getActiveMonths()
    {
        return $this->createQueryBuilder('m')
            ->select('m.createdAt')
            ->distinct(true)
            ->from('App:Media', 'media')
            ->orderBy('m.createdAt', 'DESC')
            ->where('m.isDelete = false')
            ->getQuery()
            ->getResult();
    }

    public function save(Media $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Media $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Media[] Returns an array of Media objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Media
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
