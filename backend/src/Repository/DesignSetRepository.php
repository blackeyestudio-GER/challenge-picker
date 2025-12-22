<?php

namespace App\Repository;

use App\Entity\DesignSet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DesignSet>
 */
class DesignSetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DesignSet::class);
    }

    /**
     * Find all design sets with design name, ordered by design name
     */
    public function findAllWithDesignName(): array
    {
        return $this->createQueryBuilder('ds')
            ->leftJoin('ds.designName', 'dn')
            ->addSelect('dn')
            ->orderBy('dn.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find design set by ID with all card designs
     */
    public function findWithCardDesigns(int $id): ?DesignSet
    {
        return $this->createQueryBuilder('ds')
            ->leftJoin('ds.cardDesigns', 'cd')
            ->addSelect('cd')
            ->leftJoin('ds.designName', 'dn')
            ->addSelect('dn')
            ->where('ds.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

