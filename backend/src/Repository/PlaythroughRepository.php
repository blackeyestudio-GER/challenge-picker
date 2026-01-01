<?php

namespace App\Repository;

use App\Entity\Playthrough;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Playthrough>
 */
class PlaythroughRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Playthrough::class);
    }

    public function save(Playthrough $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Playthrough $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find active playthrough for a user (status: setup, active, or paused).
     */
    public function findActiveByUser(User $user): ?Playthrough
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->andWhere('p.status IN (:statuses)')
            ->setParameter('user', $user)
            ->setParameter('statuses', [
                Playthrough::STATUS_SETUP,
                Playthrough::STATUS_ACTIVE,
                Playthrough::STATUS_PAUSED,
            ])
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find playthrough by UUID.
     */
    public function findByUuid(string $uuid): ?Playthrough
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    /**
     * Find playthrough by UUID with all related data loaded.
     */
    public function findByUuidWithRelations(string $uuid): ?Playthrough
    {
        try {
            $uuidObject = Uuid::fromString($uuid);
        } catch (\InvalidArgumentException $e) {
            return null;
        }

        return $this->createQueryBuilder('p')
            ->select('p', 'g', 'r', 'u', 'pr', 'rule')
            ->leftJoin('p.game', 'g')
            ->leftJoin('p.ruleset', 'r')
            ->leftJoin('p.user', 'u')
            ->leftJoin('p.playthroughRules', 'pr')
            ->leftJoin('pr.rule', 'rule')
            ->andWhere('p.uuid = :uuid')
            ->setParameter('uuid', $uuidObject, 'uuid')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
