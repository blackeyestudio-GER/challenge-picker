<?php

namespace App\Repository;

use App\Entity\PlaythroughRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlaythroughRule>
 */
class PlaythroughRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlaythroughRule::class);
    }

    public function save(PlaythroughRule $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PlaythroughRule $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find all active rules for a playthrough (compatible with composite primary key).
     *
     * Uses DQL with explicit foreign key column binding to avoid composite key limitations.
     *
     * @return array<PlaythroughRule>
     */
    public function findActiveByPlaythrough(\App\Entity\Playthrough $playthrough): array
    {
        // Use native SQL to get IDs, then fetch entities properly
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id');

        $sql = '
            SELECT pr.id
            FROM playthrough_rules pr
            WHERE pr.playthrough_id = ?
              AND pr.playthrough_user_uuid = ?
              AND pr.is_active = 1
        ';

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter(1, $playthrough->getId());
        $query->setParameter(2, $playthrough->getUser()->getUuid()->toBinary());

        $ids = array_column($query->getResult(), 'id');

        if (empty($ids)) {
            return [];
        }

        // Fetch entities and manually set the playthrough reference to avoid proxy conflicts
        $entities = $this->createQueryBuilder('pr')
            ->where('pr.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        // Override the playthrough proxy with the actual managed instance
        $reflection = new \ReflectionClass(PlaythroughRule::class);
        $property = $reflection->getProperty('playthrough');
        $property->setAccessible(true);

        foreach ($entities as $entity) {
            $property->setValue($entity, $playthrough);
        }

        return $entities;
    }
}
