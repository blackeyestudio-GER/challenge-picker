<?php

namespace App\Repository;

use App\Entity\UserDesignSet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<UserDesignSet>
 */
class UserDesignSetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserDesignSet::class);
    }

    /**
     * Find all design sets owned by a user.
     *
     * @return UserDesignSet[]
     */
    public function findByUser(Uuid $userUuid): array
    {
        return $this->createQueryBuilder('uds')
            ->where('uds.userUuid = :userUuid')
            ->setParameter('userUuid', $userUuid, 'uuid')
            ->orderBy('uds.purchasedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Check if user owns a specific design set.
     */
    public function userOwnsDesignSet(Uuid $userUuid, int $designSetId): bool
    {
        $count = $this->createQueryBuilder('uds')
            ->select('COUNT(uds.id)')
            ->where('uds.userUuid = :userUuid')
            ->andWhere('uds.designSet = :designSetId')
            ->setParameter('userUuid', $userUuid, 'uuid')
            ->setParameter('designSetId', $designSetId)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
