<?php

namespace App\Repository;

use App\Entity\RulesetVote;
use App\Entity\User;
use App\Entity\Ruleset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RulesetVote>
 */
class RulesetVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RulesetVote::class);
    }

    public function findVote(User $user, Ruleset $ruleset): ?RulesetVote
    {
        return $this->findOneBy([
            'user' => $user,
            'ruleset' => $ruleset
        ]);
    }

    /**
     * Get vote count for a ruleset (sum of vote_type: upvotes - downvotes)
     */
    public function getVoteCount(Ruleset $ruleset): int
    {
        $qb = $this->createQueryBuilder('v')
            ->select('COALESCE(SUM(v.voteType), 0)')
            ->where('v.ruleset = :ruleset')
            ->setParameter('ruleset', $ruleset);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Check if user has voted for a ruleset
     */
    public function hasUserVoted(User $user, Ruleset $ruleset): bool
    {
        return $this->findVote($user, $ruleset) !== null;
    }

    /**
     * Get vote information for multiple rulesets for a user
     * @return array Map of ruleset ID => ['voteType' => int|null]
     */
    public function getUserVotesForRulesets(User $user, array $rulesetIds): array
    {
        if (empty($rulesetIds)) {
            return [];
        }

        $result = $this->createQueryBuilder('v')
            ->select('IDENTITY(v.ruleset) as rulesetId, v.voteType')
            ->where('v.user = :user')
            ->andWhere('v.ruleset IN (:rulesetIds)')
            ->setParameter('user', $user)
            ->setParameter('rulesetIds', $rulesetIds)
            ->getQuery()
            ->getResult();

        $map = [];
        foreach ($result as $row) {
            $map[$row['rulesetId']] = ['voteType' => $row['voteType']];
        }

        return $map;
    }
}
