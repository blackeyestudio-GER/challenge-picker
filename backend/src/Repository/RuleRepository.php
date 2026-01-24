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
     * Find all rules for a specific ruleset.
     *
     * @return array<Rule>
     */
    public function findByRuleset(int $rulesetId): array
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.rulesets', 'rs')
            ->where('rs.id = :rulesetId')
            ->setParameter('rulesetId', $rulesetId)
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search rules by name or description.
     *
     * @return array<Rule>
     */
    public function searchRules(string $query, int $limit, int $offset, bool $withoutIcon = false): array
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.name LIKE :query')
            ->orWhere('r.description LIKE :query')
            ->setParameter('query', '%' . $query . '%');

        if ($withoutIcon) {
            $qb->andWhere('r.iconIdentifier IS NULL OR r.iconIdentifier = \'\'');
        }

        return $qb->orderBy('r.name', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count search results.
     */
    public function countSearchResults(string $query, bool $withoutIcon = false): int
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.name LIKE :query')
            ->orWhere('r.description LIKE :query')
            ->setParameter('query', '%' . $query . '%');

        if ($withoutIcon) {
            $qb->andWhere('r.iconIdentifier IS NULL OR r.iconIdentifier = \'\'');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Find rules without icons.
     *
     * @return array<Rule>
     */
    public function findRulesWithoutIcon(int $limit, int $offset): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.iconIdentifier IS NULL OR r.iconIdentifier = \'\'')
            ->orderBy('r.name', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count rules without icons.
     */
    public function countRulesWithoutIcon(): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.iconIdentifier IS NULL OR r.iconIdentifier = \'\'')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
