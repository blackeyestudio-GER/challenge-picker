<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function save(Game $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Game $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find all games ordered by name (active first, then inactive).
     *
     * @return array<Game>
     */
    public function findAllOrdered(?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->createQueryBuilder('g')
            ->orderBy('g.isActive', 'DESC')
            ->addOrderBy('g.name', 'ASC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Search all games by name or description (active first, then inactive).
     *
     * @return array<Game>
     */
    public function searchGames(string $search, int $limit, int $offset): array
    {
        return $this->createQueryBuilder('g')
            ->where('g.name LIKE :search OR g.description LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('g.isActive', 'DESC')
            ->addOrderBy('g.name', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count all search results.
     */
    public function countSearchResults(string $search): int
    {
        return (int) $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->where('g.name LIKE :search OR g.description LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
