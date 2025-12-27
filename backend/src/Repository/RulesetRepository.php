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
     * Find all rulesets assigned to a specific game.
     * Includes both:
     * 1. Game-specific rulesets (direct connection)
     * 2. Category-universal rulesets (via category representative games).
     *
     * @return array<Ruleset>
     */
    public function findByGame(int $gameId): array
    {
        $em = $this->getEntityManager();

        // Step 1: Get game-specific rulesets
        $gameRulesets = $this->createQueryBuilder('r')
            ->join('r.games', 'g')
            ->where('g.id = :gameId')
            ->setParameter('gameId', $gameId)
            ->getQuery()
            ->getResult();

        // Step 2: Get category representative game IDs for this game
        $repGameIds = $em->createQueryBuilder()
            ->select('IDENTITY(c.representativeGame)')
            ->from('App\Entity\Category', 'c')
            ->join('c.games', 'g')
            ->where('g.id = :gameId')
            ->andWhere('c.representativeGame IS NOT NULL')
            ->setParameter('gameId', $gameId)
            ->getQuery()
            ->getSingleColumnResult();

        $categoryRulesets = [];
        if (!empty($repGameIds)) {
            // Step 3: Get rulesets connected to representative games
            $categoryRulesets = $this->createQueryBuilder('r')
                ->join('r.games', 'g')
                ->where('g.id IN (:repGameIds)')
                ->setParameter('repGameIds', $repGameIds)
                ->getQuery()
                ->getResult();
        }

        // Step 4: Merge and deduplicate
        $uniqueRulesets = [];
        foreach (array_merge($gameRulesets, $categoryRulesets) as $ruleset) {
            $uniqueRulesets[$ruleset->getId()] = $ruleset;
        }

        // Sort by name
        usort($uniqueRulesets, fn ($a, $b) => strcmp($a->getName(), $b->getName()));

        return array_values($uniqueRulesets);
    }
}
