<?php

namespace App\Repository;

use App\Entity\Challenge;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Challenge>
 */
class ChallengeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Challenge::class);
    }

    /**
     * Find all pending challenges for a user.
     *
     * @return array<Challenge>
     */
    public function findPendingChallengesForUser(Uuid $userUuid): array
    {
        /** @var array<Challenge> $result */
        $result = $this->createQueryBuilder('c')
            ->join('c.challengedUser', 'u')
            ->where('u.uuid = :userUuid')
            ->andWhere('c.status = :status')
            ->andWhere('c.expiresAt > :now')
            ->setParameter('userUuid', $userUuid, 'uuid')
            ->setParameter('status', Challenge::STATUS_PENDING)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Find all challenges sent by a user.
     *
     * @return array<Challenge>
     */
    public function findChallengesSentByUser(Uuid $userUuid): array
    {
        /** @var array<Challenge> $result */
        $result = $this->createQueryBuilder('c')
            ->select('c', 'challenger', 'challenged', 'source', 'resulting', 'sourceGame', 'sourceRuleset', 'sourceUser')
            ->join('c.challenger', 'challenger')
            ->leftJoin('c.challengedUser', 'challenged')
            ->leftJoin('c.sourcePlaythrough', 'source')
            ->leftJoin('source.game', 'sourceGame')
            ->leftJoin('source.ruleset', 'sourceRuleset')
            ->leftJoin('source.user', 'sourceUser')
            ->leftJoin('c.resultingPlaythrough', 'resulting')
            ->where('challenger.uuid = :userUuid')
            ->setParameter('userUuid', $userUuid, 'uuid')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Find all challenges from a source playthrough (to get all participants).
     *
     * @return array<Challenge>
     */
    public function findChallengesBySourcePlaythrough(int $playthroughId, Uuid $playthroughUserUuid): array
    {
        /** @var array<Challenge> $result */
        $result = $this->createQueryBuilder('c')
            ->select('c', 'challenger', 'challenged', 'source', 'resulting', 'sourceUser')
            ->leftJoin('c.challenger', 'challenger')
            ->leftJoin('c.challengedUser', 'challenged')
            ->leftJoin('c.sourcePlaythrough', 'source')
            ->leftJoin('source.user', 'sourceUser')
            ->leftJoin('c.resultingPlaythrough', 'resulting')
            ->where('source.id = :playthroughId')
            ->andWhere('sourceUser.uuid = :playthroughUserUuid')
            ->setParameter('playthroughId', $playthroughId)
            ->setParameter('playthroughUserUuid', $playthroughUserUuid, 'uuid')
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Check if a challenge already exists between two users for a specific playthrough.
     */
    public function hasPendingChallenge(Uuid $challengerUuid, Uuid $challengedUuid, int $playthroughId, Uuid $playthroughUserUuid): bool
    {
        $count = $this->createQueryBuilder('c')
            ->select('COUNT(c.uuid)')
            ->join('c.challenger', 'challenger')
            ->join('c.challengedUser', 'challenged')
            ->join('c.sourcePlaythrough', 'p')
            ->where('challenger.uuid = :challengerUuid')
            ->andWhere('challenged.uuid = :challengedUuid')
            ->andWhere('p.id = :playthroughId')
            ->andWhere('p.user = (SELECT u FROM App\Entity\User u WHERE u.uuid = :playthroughUserUuid)')
            ->andWhere('c.status = :status')
            ->andWhere('c.expiresAt > :now')
            ->setParameter('challengerUuid', $challengerUuid, 'uuid')
            ->setParameter('challengedUuid', $challengedUuid, 'uuid')
            ->setParameter('playthroughId', $playthroughId)
            ->setParameter('playthroughUserUuid', $playthroughUserUuid, 'uuid')
            ->setParameter('status', Challenge::STATUS_PENDING)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
