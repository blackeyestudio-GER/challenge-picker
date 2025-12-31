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

        // Step 2: Get category representative game IDs and category info for this game
        $categoryInfo = $em->createQueryBuilder()
            ->select('IDENTITY(c.representativeGame) as repGameId', 'c.id as categoryId', 'c.name as categoryName')
            ->from('App\Entity\Category', 'c')
            ->join('c.games', 'g')
            ->where('g.id = :gameId')
            ->andWhere('c.representativeGame IS NOT NULL')
            ->setParameter('gameId', $gameId)
            ->getQuery()
            ->getResult();

        $repGameIds = array_filter(array_column($categoryInfo, 'repGameId'));
        $categoryMap = [];
        foreach ($categoryInfo as $info) {
            if ($info['repGameId']) {
                $categoryMap[$info['repGameId']] = [
                    'id' => $info['categoryId'],
                    'name' => $info['categoryName'],
                ];
            }
        }

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

    /**
     * Find rulesets for a game with metadata about their source (game-specific vs category-based).
     * Returns an array with 'ruleset', 'isGameSpecific', and 'categoryName' keys.
     *
     * @return array<array{ruleset: Ruleset, isGameSpecific: bool, categoryName: ?string, categoryId: ?int}>
     */
    public function findByGameWithMetadata(int $gameId): array
    {
        $em = $this->getEntityManager();

        // Step 1: Get game-specific rulesets
        $gameRulesets = $this->createQueryBuilder('r')
            ->join('r.games', 'g')
            ->where('g.id = :gameId')
            ->setParameter('gameId', $gameId)
            ->getQuery()
            ->getResult();

        $gameSpecificIds = array_map(fn ($r) => $r->getId(), $gameRulesets);

        // Step 2: Get category representative game IDs and category info for this game
        $categoryInfo = $em->createQueryBuilder()
            ->select('IDENTITY(c.representativeGame) as repGameId', 'c.id as categoryId', 'c.name as categoryName')
            ->from('App\Entity\Category', 'c')
            ->join('c.games', 'g')
            ->where('g.id = :gameId')
            ->andWhere('c.representativeGame IS NOT NULL')
            ->setParameter('gameId', $gameId)
            ->getQuery()
            ->getResult();

        $repGameIds = array_filter(array_column($categoryInfo, 'repGameId'));
        $categoryMap = [];
        foreach ($categoryInfo as $info) {
            if ($info['repGameId']) {
                $categoryMap[$info['repGameId']] = [
                    'id' => $info['categoryId'],
                    'name' => $info['categoryName'],
                ];
            }
        }

        $categoryRulesets = [];
        if (!empty($repGameIds)) {
            // Step 3: Get rulesets connected to representative games
            $categoryRulesetsRaw = $this->createQueryBuilder('r')
                ->join('r.games', 'g')
                ->where('g.id IN (:repGameIds)')
                ->setParameter('repGameIds', $repGameIds)
                ->getQuery()
                ->getResult();

            // Map category rulesets with their category info
            foreach ($categoryRulesetsRaw as $ruleset) {
                // Find which representative game this ruleset is connected to
                foreach ($ruleset->getGames() as $game) {
                    $gameId = $game->getId();
                    if ($gameId && isset($categoryMap[$gameId])) {
                        $categoryRulesets[] = [
                            'ruleset' => $ruleset,
                            'categoryId' => $categoryMap[$gameId]['id'],
                            'categoryName' => $categoryMap[$gameId]['name'],
                        ];
                        break; // Only need first match
                    }
                }
            }
        }

        // Step 4: Build result array
        $result = [];

        // Add game-specific rulesets
        foreach ($gameRulesets as $ruleset) {
            $result[] = [
                'ruleset' => $ruleset,
                'isGameSpecific' => true,
                'categoryName' => null,
                'categoryId' => null,
            ];
        }

        // Add category-based rulesets (avoid duplicates)
        $addedCategoryRulesetIds = [];
        foreach ($categoryRulesets as $item) {
            $rulesetId = $item['ruleset']->getId();
            if (!in_array($rulesetId, $gameSpecificIds) && !in_array($rulesetId, $addedCategoryRulesetIds)) {
                $result[] = [
                    'ruleset' => $item['ruleset'],
                    'isGameSpecific' => false,
                    'categoryName' => $item['categoryName'],
                    'categoryId' => $item['categoryId'],
                ];
                $addedCategoryRulesetIds[] = $rulesetId;
            }
        }

        // Sort by name
        usort($result, fn ($a, $b) => strcmp($a['ruleset']->getName(), $b['ruleset']->getName()));

        return $result;
    }
}
