<?php

namespace App\Repository;

use App\Entity\DesignName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DesignName>
 */
class DesignNameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DesignName::class);
    }

    /**
     * Find all design names ordered by name.
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
