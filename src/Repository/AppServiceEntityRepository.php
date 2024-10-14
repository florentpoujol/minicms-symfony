<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository as DoctrineServiceEntityRepository;

/**
 * @template T of object
 * @template-extends DoctrineServiceEntityRepository<T>
 */
abstract class AppServiceEntityRepository extends DoctrineServiceEntityRepository
{
    /**
     * Finds a single entity by a set of criteria.
     *
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     *
     * @return T
     */
    public function findOneByOrThrow(array $criteria, ?array $orderBy = null): object
    {
        $entity = $this->findOneBy($criteria, $orderBy);
        if ($entity === null) {
            throw new \UnexpectedValueException();
        }

        return $entity;
    }
}