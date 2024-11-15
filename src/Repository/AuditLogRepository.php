<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\AuditLog;
use App\Entity\DoctrineEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AuditLog>
 */
final class AuditLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditLog::class);
    }

    public function getLast(): AuditLog
    {
        return $this->createQueryBuilder('al') // @phpstan-ignore-line (should return AuditLog but returns mixed) (this is because what returns getQuery() isn't generic)
           ->orderBy('al.id', 'DESC')
           ->setMaxResults(1)
           ->getQuery()
           ->getSingleResult()
        ;
    }

    /**
     * @return ArrayCollection<int, AuditLog>
     */
    public function getForEntity(
        DoctrineEntity $entity,
        int $page = 1,
        int $perPage = 50,
    ): ArrayCollection {
        /** @var array<int, AuditLog> $array */
        $array = $this->createQueryBuilder('al')

            ->andWhere('al.entity_id = :entity_id')
            ->setParameter('entity_id', $entity->getId())

            ->andWhere('al.entity_type = :entity_type')
            ->setParameter('entity_type', AuditLog::getTypeForEntity($entity::class))

            // eager load user's email
            ->leftJoin('al.user', 'u')
            ->addSelect('u') // not sure how to only ask for user's id and email
            ->andWhere('al.user = u.id')

            ->orderBy('al.id', 'DESC')

            ->setFirstResult($perPage * ($page - 1))
            ->setMaxResults($perPage)

            ->getQuery()
            ->getResult()
        ;

        return new ArrayCollection($array);
    }

//    /**
//     * @return AuditLog[] Returns an array of AuditLog objects
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

//    public function findOneBySomeField($value): ?AuditLog
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
