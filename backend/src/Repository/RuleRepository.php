<?php

namespace App\Repository;

use App\Entity\Rule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rule>
 */
class RuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rule::class);
    }

    public function save(Rule $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Rule $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find all rules for a specific ruleset
     *
     * @return Rule[]
     */
    public function findByRuleset(int $rulesetId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.ruleset = :rulesetId')
            ->setParameter('rulesetId', $rulesetId)
            ->orderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

