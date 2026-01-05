<?php

namespace App\Repository;

use App\Entity\Playthrough;
use App\Entity\PlaythroughRuleQueue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlaythroughRuleQueue>
 */
class PlaythroughRuleQueueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlaythroughRuleQueue::class);
    }

    /**
     * Get next position for a playthrough's queue.
     */
    public function getNextPosition(Playthrough $playthrough): int
    {
        $result = $this->createQueryBuilder('q')
            ->select('MAX(q.position)')
            ->where('q.playthrough = :playthroughId')
            ->andWhere('q.status IN (:statuses)')
            ->setParameter('playthroughId', $playthrough->getId())
            ->setParameter('statuses', [
                PlaythroughRuleQueue::STATUS_PENDING,
                PlaythroughRuleQueue::STATUS_PROCESSING,
            ])
            ->getQuery()
            ->getSingleScalarResult();

        return ((int) $result) + 1;
    }

    /**
     * Get pending queue entries for a playthrough.
     *
     * @return array<PlaythroughRuleQueue>
     */
    public function getPendingQueue(Playthrough $playthrough): array
    {
        /* @var array<PlaythroughRuleQueue> */
        return $this->createQueryBuilder('q')
            ->where('q.playthrough = :playthroughId')
            ->andWhere('q.status = :status')
            ->setParameter('playthroughId', $playthrough->getId())
            ->setParameter('status', PlaythroughRuleQueue::STATUS_PENDING)
            ->orderBy('q.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get next pending queue entry for processing.
     */
    public function getNextPending(Playthrough $playthrough): ?PlaythroughRuleQueue
    {
        $result = $this->createQueryBuilder('q')
            ->where('q.playthrough = :playthroughId')
            ->andWhere('q.status = :status')
            ->setParameter('playthroughId', $playthrough->getId())
            ->setParameter('status', PlaythroughRuleQueue::STATUS_PENDING)
            ->orderBy('q.position', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result instanceof PlaythroughRuleQueue ? $result : null;
    }

    /**
     * Count pending entries in queue.
     */
    public function countPending(Playthrough $playthrough): int
    {
        return (int) $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->where('q.playthrough = :playthroughId')
            ->andWhere('q.status = :status')
            ->setParameter('playthroughId', $playthrough->getId())
            ->setParameter('status', PlaythroughRuleQueue::STATUS_PENDING)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
