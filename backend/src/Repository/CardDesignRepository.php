<?php

namespace App\Repository;

use App\Entity\CardDesign;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CardDesign>
 */
class CardDesignRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CardDesign::class);
    }

    /**
     * Find all card designs for a specific design set
     */
    public function findByDesignSet(int $designSetId): array
    {
        return $this->createQueryBuilder('cd')
            ->where('cd.designSet = :designSetId')
            ->setParameter('designSetId', $designSetId)
            ->orderBy('cd.cardIdentifier', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

