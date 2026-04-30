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
     * Find all design sets with design name, ordered by design name.
     *
     * @return array<int, DesignSet>
     */
    public function findAllWithDesignName(): array
    {
        /** @var array<int, DesignSet> $result */
        $result = $this->createQueryBuilder('ds')
            ->leftJoin('ds.designName', 'dn')
            ->addSelect('dn')
            ->orderBy('dn.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Find design set by ID with all card designs.
     */
    public function findWithCardDesigns(int $id): ?DesignSet
    {
        $result = $this->createQueryBuilder('ds')
            ->leftJoin('ds.cardDesigns', 'cd')
            ->addSelect('cd')
            ->leftJoin('ds.designName', 'dn')
            ->addSelect('dn')
            ->where('ds.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        return $result instanceof DesignSet ? $result : null;
    }
}
