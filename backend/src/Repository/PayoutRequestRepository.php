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
     *
     * @return list<PayoutRequest>
     */
    public function findPendingByDesigner(User $designer): array
    {
        /** @var list<PayoutRequest> $result */
        $result = $this->createQueryBuilder('pr')
            ->where('pr.designer = :designer')
            ->andWhere('pr.status = :status')
            ->setParameter('designer', $designer)
            ->setParameter('status', PayoutRequest::STATUS_PENDING)
            ->orderBy('pr.requestedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Get all payout requests for a designer.
     *
     * @return list<PayoutRequest>
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

        /** @var list<PayoutRequest> $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * Get all pending payout requests (for admin).
     *
     * @return list<PayoutRequest>
     */
    public function findPending(): array
    {
        /** @var list<PayoutRequest> $result */
        $result = $this->createQueryBuilder('pr')
            ->where('pr.status = :status')
            ->setParameter('status', PayoutRequest::STATUS_PENDING)
            ->orderBy('pr.requestedAt', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Get total pending amount for a designer.
     *
     * @return numeric-string
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

        if (!is_string($result) || !is_numeric($result)) {
            return '0.00';
        }

        /** @var numeric-string $result */
        return $result;
    }
}
