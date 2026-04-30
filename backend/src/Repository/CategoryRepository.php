<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Find category by ID.
     */
    public function findById(int $id): ?Category
    {
        /** @var Category|null $result */
        $result = $this->find($id);

        return $result;
    }

    /**
     * Find category by slug.
     */
    public function findBySlug(string $slug): ?Category
    {
        /** @var Category|null $result */
        $result = $this->findOneBy(['slug' => $slug]);

        return $result;
    }

    /**
     * Find all categories ordered by name.
     *
     * @return array<Category>
     */
    public function findAllOrdered(): array
    {
        /** @var array<Category> $result */
        $result = $this->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }
}
