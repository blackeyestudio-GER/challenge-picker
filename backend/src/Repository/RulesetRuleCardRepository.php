<?php

namespace App\Repository;

use App\Entity\RulesetRuleCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RulesetRuleCard>
 */
class RulesetRuleCardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RulesetRuleCard::class);
    }

    public function save(RulesetRuleCard $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RulesetRuleCard $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find all rule cards for a specific ruleset.
     *
     * @return array<RulesetRuleCard>
     */
    public function findByRuleset(int $rulesetId): array
    {
        return $this->createQueryBuilder('rrc')
            ->where('rrc.ruleset = :rulesetId')
            ->setParameter('rulesetId', $rulesetId)
            ->orderBy('rrc.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
