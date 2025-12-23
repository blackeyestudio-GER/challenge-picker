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
     * Find all rulesets for a specific game, including inherited rulesets
     * from category representative games
     *
     * @return Ruleset[]
     */
    public function findByGame(int $gameId): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        // Query to get ruleset IDs (direct + inherited from category representative games)
        $sql = '
            SELECT DISTINCT r.id 
            FROM rulesets r
            WHERE r.game_id = :gameId
            
            UNION
            
            SELECT DISTINCT r.id 
            FROM rulesets r
            WHERE r.game_id IN (
                -- Find representative games for categories this game belongs to
                SELECT g.id 
                FROM games g
                JOIN game_categories gc ON g.id = gc.game_id
                WHERE g.is_category_representative = 1
                AND gc.category_id IN (
                    -- Find categories this game belongs to
                    SELECT gc2.category_id 
                    FROM game_categories gc2
                    WHERE gc2.game_id = :gameId
                )
            )
        ';
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['gameId' => $gameId]);
        $rulesetIds = array_column($result->fetchAllAssociative(), 'id');
        
        if (empty($rulesetIds)) {
            return [];
        }
        
        // Fetch the full ruleset entities using the IDs
        return $this->createQueryBuilder('r')
            ->where('r.id IN (:rulesetIds)')
            ->setParameter('rulesetIds', $rulesetIds)
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

