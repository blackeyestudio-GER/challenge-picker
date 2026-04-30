<?php

namespace App\Repository;

use App\Entity\RuleIcon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RuleIcon>
 */
class RuleIconRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RuleIcon::class);
    }

    /**
     * Find icons by category.
     *
     * @return RuleIcon[]
     */
    public function findByCategory(string $category): array
    {
        /** @var list<RuleIcon> $result */
        $result = $this->createQueryBuilder('ri')
            ->where('ri.category = :category')
            ->setParameter('category', $category)
            ->orderBy('ri.displayName', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Search icons by name or tags.
     *
     * @return RuleIcon[]
     */
    public function search(string $query): array
    {
        /** @var list<RuleIcon> $result */
        $result = $this->createQueryBuilder('ri')
            ->where('ri.displayName LIKE :query')
            ->orWhere('ri.identifier LIKE :query')
            ->orWhere('JSON_SEARCH(ri.tags, \'one\', :searchQuery) IS NOT NULL')
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('searchQuery', '%' . $query . '%')
            ->orderBy('ri.displayName', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Get all icons grouped by category.
     *
     * @return array<string, RuleIcon[]>
     */
    public function findAllGroupedByCategory(): array
    {
        /** @var list<RuleIcon> $icons */
        $icons = $this->createQueryBuilder('ri')
            ->orderBy('ri.category', 'ASC')
            ->addOrderBy('ri.displayName', 'ASC')
            ->getQuery()
            ->getResult();

        /** @var array<string, list<RuleIcon>> $grouped */
        $grouped = [];
        foreach ($icons as $icon) {
            $category = $icon->getCategory();
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $icon;
        }

        return $grouped;
    }
}
