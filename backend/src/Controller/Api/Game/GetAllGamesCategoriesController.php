<?php

namespace App\Controller\Api\Game;

use App\DTO\Response\Game\GameCategoryResponse;
use App\Entity\User;
use App\Repository\GameCategoryVoteRepository;
use App\Repository\GameRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/games/categories', name: 'api_games_categories_batch', methods: ['GET'])]
class GetAllGamesCategoriesController extends AbstractController
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly GameCategoryVoteRepository $voteRepository,
        private readonly Connection $connection
    ) {
    }

    /**
     * Get all categories for all games in a single request (batch endpoint).
     * Returns a map: { gameId: [categories...] }.
     *
     * @param User|null $user Current user (optional)
     *
     * @return JsonResponse Map of game IDs to their categories
     */
    public function __invoke(#[CurrentUser] ?User $user = null): JsonResponse
    {
        try {
            $games = $this->gameRepository->findAllOrdered();
            $result = [];

            // Get all user votes in one query if user is provided
            /** @var array<int, array<int, int>> $userVoteMap */
            $userVoteMap = [];
            if ($user) {
                /** @var list<array{gameId: int|string, categoryId: int|string, voteType: int}> $userVotes */
                $userVotes = $this->voteRepository->createQueryBuilder('v')
                    ->select('IDENTITY(v.game) as gameId', 'IDENTITY(v.category) as categoryId', 'v.voteType')
                    ->where('v.user = :user')
                    ->setParameter('user', $user)
                    ->getQuery()
                    ->getResult();

                foreach ($userVotes as $vote) {
                    $gameId = (int) $vote['gameId'];
                    $categoryId = (int) $vote['categoryId'];
                    if ($gameId < 1 || $categoryId < 1) {
                        continue;
                    }
                    if (!isset($userVoteMap[$gameId])) {
                        $userVoteMap[$gameId] = [];
                    }
                    $userVoteMap[$gameId][$categoryId] = $vote['voteType'];
                }
            }

            // Get all game-category associations and vote counts in one query
            $sql = '
                SELECT 
                    gc.game_id as gameId,
                    c.id as categoryId,
                    c.name,
                    c.slug,
                    COALESCE(SUM(gcv.vote_type), 0) as voteCount
                FROM game_categories gc
                INNER JOIN categories c ON gc.category_id = c.id
                LEFT JOIN game_category_votes gcv ON gc.game_id = gcv.game_id AND gc.category_id = gcv.category_id
                GROUP BY gc.game_id, c.id, c.name, c.slug
                ORDER BY gc.game_id, voteCount DESC, c.name ASC
            ';

            /** @var array<int, array<string, mixed>> $allResults */
            /** @var list<array{gameId: int|string, categoryId: int|string, name: string, slug: string, voteCount: int|string}> $allResults */
            $allResults = $this->connection->executeQuery($sql)->fetchAllAssociative();

            // Group by game ID
            foreach ($allResults as $row) {
                $gameId = (int) $row['gameId'];
                $categoryId = (int) $row['categoryId'];
                if ($gameId < 1 || $categoryId < 1) {
                    continue;
                }

                if (!isset($result[$gameId])) {
                    $result[$gameId] = [];
                }

                $categoryData = [
                    'id' => $categoryId,
                    'name' => $row['name'],
                    'slug' => $row['slug'],
                    'voteCount' => (int) $row['voteCount'],
                    'userVoted' => false,
                    'userVoteType' => null,
                ];

                // Add user vote info if available
                if ($user instanceof User && isset($userVoteMap[$gameId][$categoryId])) {
                    $categoryData['userVoted'] = true;
                    $categoryData['userVoteType'] = (int) $userVoteMap[$gameId][$categoryId];
                }

                $result[$gameId][] = GameCategoryResponse::fromArray($categoryData);
            }

            // Ensure all games have an entry (even if empty)
            foreach ($games as $game) {
                if (!isset($result[$game->getId()])) {
                    $result[$game->getId()] = [];
                }
            }

            return $this->json([
                'success' => true,
                'data' => $result,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            error_log('Failed to fetch all games categories: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FETCH_FAILED',
                    'message' => 'Failed to fetch games categories',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
