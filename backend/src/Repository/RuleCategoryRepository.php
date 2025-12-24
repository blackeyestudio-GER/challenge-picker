<?php

namespace App\Repository;

use App\Entity\RuleCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RuleCategory>
 */
class RuleCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RuleCategory::class);
    }

    public function save(RuleCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RuleCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Get recommended rules for a category (sorted by relevance score + usage).
     *
     * @return array<RuleCategory>
     */
    public function getRecommendedRulesForCategory(int $categoryId): array
    {
        return $this->createQueryBuilder('rc')
            ->where('rc.category = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->orderBy('rc.manualRelevanceScore', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
