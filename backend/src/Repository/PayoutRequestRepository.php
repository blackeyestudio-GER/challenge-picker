<?php

namespace App\Repository;

use App\Entity\PayoutRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PayoutRequest>
 */
class PayoutRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PayoutRequest::class);
    }

    /**
     * Get pending payout requests for a designer.
     */
    public function findPendingByDesigner(User $designer): array
    {
        return $this->createQueryBuilder('pr')
            ->where('pr.designer = :designer')
            ->andWhere('pr.status = :status')
            ->setParameter('designer', $designer)
            ->setParameter('status', PayoutRequest::STATUS_PENDING)
            ->orderBy('pr.requestedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all payout requests for a designer.
     */
    public function findByDesigner(User $designer, ?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->createQueryBuilder('pr')
            ->where('pr.designer = :designer')
            ->setParameter('designer', $designer)
            ->orderBy('pr.requestedAt', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get all pending payout requests (for admin).
     */
    public function findPending(): array
    {
        return $this->createQueryBuilder('pr')
            ->where('pr.status = :status')
            ->setParameter('status', PayoutRequest::STATUS_PENDING)
            ->orderBy('pr.requestedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get total pending amount for a designer.
     */
    public function getTotalPendingAmount(User $designer): string
    {
        $result = $this->createQueryBuilder('pr')
            ->select('SUM(pr.amount) as total')
            ->where('pr.designer = :designer')
            ->andWhere('pr.status = :status')
            ->setParameter('designer', $designer)
            ->setParameter('status', PayoutRequest::STATUS_PENDING)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? '0.00';
    }
}
