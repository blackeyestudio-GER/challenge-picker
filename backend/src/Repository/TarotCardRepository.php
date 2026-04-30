<?php

namespace App\Repository;

use App\Entity\TarotCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TarotCard>
 */
class TarotCardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TarotCard::class);
    }

    /**
     * Get all cards ordered by sortOrder.
     *
     * @return array<TarotCard>
     */
    public function findAllOrdered(): array
    {
        /** @var array<TarotCard> $result */
        $result = $this->createQueryBuilder('tc')
            ->orderBy('tc.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }
}
