<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
final class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @return array<Article>
     */
    public function getAllPublished(): array
    {
        // @phpstan-ignore-next-line (should return ... but returns mixed)
        return $this->getEntityManager()
            ->createQuery(<<<DQL
            SELECT a, u
            FROM App\Entity\Article a
            INNER JOIN a.user u
            WHERE
            a.user = u.id
            and a.published_at is not null
            order by a.published_at desc
            DQL)
            ->getResult()
        ;

        /*
        without eager loading this query can work :

        return $this->createQueryBuilder('a')
            ->andWhere('a.published_at is not null')
            ->orderBy('a.published_at', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
        */
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
