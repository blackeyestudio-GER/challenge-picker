<?php

namespace App\Repository;

use App\Entity\DesignerEarnings;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DesignerEarnings>
 */
class DesignerEarningsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DesignerEarnings::class);
    }

    /**
     * Get total earnings for a designer.
     *
     * @return numeric-string
     */
    public function getTotalEarnings(User $designer): string
    {
        $result = $this->createQueryBuilder('de')
            ->select('SUM(de.amount) as total')
            ->where('de.designer = :designer')
            ->setParameter('designer', $designer)
            ->getQuery()
            ->getSingleScalarResult();

        if (!is_string($result) || !is_numeric($result)) {
            return '0.00';
        }

        /** @var numeric-string $result */
        return $result;
    }

    /**
     * Get earnings for a specific design set.
     *
     * @return numeric-string
     */
    public function getEarningsForDesignSet(User $designer, int $designSetId): string
    {
        $result = $this->createQueryBuilder('de')
            ->select('SUM(de.amount) as total')
            ->where('de.designer = :designer')
            ->andWhere('de.designSet = :designSetId')
            ->setParameter('designer', $designer)
            ->setParameter('designSetId', $designSetId)
            ->getQuery()
            ->getSingleScalarResult();

        if (!is_string($result) || !is_numeric($result)) {
            return '0.00';
        }

        /** @var numeric-string $result */
        return $result;
    }

    /**
     * Get purchase count for a design set.
     */
    public function getPurchaseCountForDesignSet(int $designSetId): int
    {
        $result = $this->createQueryBuilder('de')
            ->select('COUNT(de.id)')
            ->where('de.designSet = :designSetId')
            ->setParameter('designSetId', $designSetId)
            ->getQuery()
            ->getSingleScalarResult();

        return is_numeric($result) ? (int) $result : 0;
    }

    /**
     * Get all earnings for a designer grouped by design set.
     *
     * @return list<array{designSetId: int|string, designName: string, totalEarnings: string, purchaseCount: int|string}>
     */
    public function getEarningsByDesignSet(User $designer): array
    {
        /** @var list<array{designSetId: int|string, designName: string, totalEarnings: string, purchaseCount: int|string}> $result */
        $result = $this->createQueryBuilder('de')
            ->select('ds.id as designSetId', 'dn.name as designName', 'SUM(de.amount) as totalEarnings', 'COUNT(de.id) as purchaseCount')
            ->join('de.designSet', 'ds')
            ->join('ds.designName', 'dn')
            ->where('de.designer = :designer')
            ->setParameter('designer', $designer)
            ->groupBy('ds.id', 'dn.name')
            ->getQuery()
            ->getResult();

        return $result;
    }
}
