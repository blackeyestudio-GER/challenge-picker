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
     *
     * @return array<int, DesignName>
     */
    public function findAllOrdered(): array
    {
        /** @var array<int, DesignName> $result */
        $result = $this->createQueryBuilder('d')
            ->orderBy('d.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }
}
