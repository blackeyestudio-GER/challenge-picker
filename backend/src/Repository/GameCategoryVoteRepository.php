<?php

namespace App\Repository;

use App\Entity\GameCategoryVote;
use App\Entity\Game;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameCategoryVote>
 */
class GameCategoryVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameCategoryVote::class);
    }

    /**
     * Find a specific vote
     */
    public function findVote(User $user, Game $game, Category $category): ?GameCategoryVote
    {
        return $this->findOneBy([
            'user' => $user,
            'game' => $game,
            'category' => $category
        ]);
    }

    /**
     * Get vote count for a game-category pair
     */
    public function getVoteCount(Game $game, Category $category): int
    {
        return $this->count([
            'game' => $game,
            'category' => $category
        ]);
    }

    /**
     * Get all categories for a game with vote counts
     * 
     * @return array [['category' => Category, 'voteCount' => int, 'userVoted' => bool], ...]
     */
    public function getCategoriesWithVotes(Game $game, ?User $user = null): array
    {
        $qb = $this->createQueryBuilder('v')
            ->select('c.id, c.name, c.slug, COUNT(v.id) as voteCount')
            ->join('v.category', 'c')
            ->where('v.game = :game')
            ->setParameter('game', $game)
            ->groupBy('c.id')
            ->orderBy('voteCount', 'DESC');

        $results = $qb->getQuery()->getResult();

        // If user is provided, check which categories they voted for
        if ($user) {
            $userVotes = $this->createQueryBuilder('v')
                ->select('IDENTITY(v.category) as categoryId')
                ->where('v.game = :game')
                ->andWhere('v.user = :user')
                ->setParameter('game', $game)
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult();

            $votedCategoryIds = array_column($userVotes, 'categoryId');

            foreach ($results as &$result) {
                $result['userVoted'] = in_array($result['id'], $votedCategoryIds);
            }
        }

        return $results;
    }

    /**
     * Check if user has voted for a game-category pair
     */
    public function hasUserVoted(User $user, Game $game, Category $category): bool
    {
        return $this->findVote($user, $game, $category) !== null;
    }
}

