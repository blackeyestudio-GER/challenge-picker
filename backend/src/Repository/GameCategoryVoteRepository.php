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
     * Get vote count for a game-category pair (sum of vote_type: upvotes - downvotes)
     */
    public function getVoteCount(Game $game, Category $category): int
    {
        $qb = $this->createQueryBuilder('v')
            ->select('COALESCE(SUM(v.voteType), 0)')
            ->where('v.game = :game')
            ->andWhere('v.category = :category')
            ->setParameter('game', $game)
            ->setParameter('category', $category);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get all categories for a game with vote counts
     * Uses game_categories for associations and game_category_votes for community voting
     * 
     * @return array [['category' => Category, 'voteCount' => int, 'userVoted' => bool], ...]
     */
    public function getCategoriesWithVotes(Game $game, ?User $user = null): array
    {
        try {
            $conn = $this->getEntityManager()->getConnection();
            $gameId = $game->getId();
            
            // Get categories from game_categories table and sum vote_type from game_category_votes
            // Using inline gameId since it's an integer (safe from SQL injection)
            $sql = sprintf('
                SELECT 
                    c.id,
                    c.name,
                    c.slug,
                    COALESCE(SUM(gcv.vote_type), 0) as voteCount
                FROM categories c
                INNER JOIN game_categories gc ON c.id = gc.category_id AND gc.game_id = %d
                LEFT JOIN game_category_votes gcv ON c.id = gcv.category_id AND gcv.game_id = %d
                GROUP BY c.id, c.name, c.slug
                ORDER BY voteCount DESC, c.name ASC
            ', $gameId, $gameId);
            
            $results = $conn->executeQuery($sql)->fetchAllAssociative();
        } catch (\Exception $e) {
            error_log('Failed to fetch categories with votes: ' . $e->getMessage());
            throw $e;
        }

        // If user is provided, check which categories they voted for and include vote type
        if ($user) {
            $userVotes = $this->createQueryBuilder('v')
                ->select('IDENTITY(v.category) as categoryId, v.voteType')
                ->where('v.game = :game')
                ->andWhere('v.user = :user')
                ->setParameter('game', $game)
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult();

            $voteMap = [];
            foreach ($userVotes as $vote) {
                $voteMap[$vote['categoryId']] = $vote['voteType'];
            }

            foreach ($results as &$result) {
                if (isset($voteMap[$result['id']])) {
                    $result['userVoted'] = true;
                    $result['userVoteType'] = $voteMap[$result['id']];
                } else {
                    $result['userVoted'] = false;
                    $result['userVoteType'] = null;
                }
            }
        } else {
            // Set userVoted to false for all categories when no user is provided
            foreach ($results as &$result) {
                $result['userVoted'] = false;
                $result['userVoteType'] = null;
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

