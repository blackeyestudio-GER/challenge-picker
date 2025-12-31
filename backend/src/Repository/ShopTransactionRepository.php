<?php

namespace App\Repository;

use App\Entity\ShopTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<ShopTransaction>
 */
class ShopTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopTransaction::class);
    }

    /**
     * Find transaction by Stripe session ID.
     */
    public function findByStripeSessionId(string $sessionId): ?ShopTransaction
    {
        return $this->createQueryBuilder('st')
            ->where('st.stripeSessionId = :sessionId')
            ->setParameter('sessionId', $sessionId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all transactions for a user.
     *
     * @return ShopTransaction[]
     */
    public function findByUser(Uuid $userUuid): array
    {
        return $this->createQueryBuilder('st')
            ->where('st.userUuid = :userUuid')
            ->setParameter('userUuid', $userUuid, 'uuid')
            ->orderBy('st.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
