<?php

namespace App\Repository;

use App\Entity\Ruleset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ruleset>
 */
class RulesetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ruleset::class);
    }

    public function save(Ruleset $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Ruleset $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find all rulesets for a specific game
     *
     * @return Ruleset[]
     */
    public function findByGame(int $gameId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.game = :gameId')
            ->setParameter('gameId', $gameId)
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

