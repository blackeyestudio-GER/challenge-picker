<?php

namespace App\Controller\Api\Game;

use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\GameCategoryVoteRepository;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/games/{gameId}/categories/{categoryId}/vote', name: 'api_game_category_unvote', methods: ['DELETE'])]
class UnvoteGameCategoryController extends AbstractController
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly GameCategoryVoteRepository $voteRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Remove vote for a category on a game.
     *
     * @param int $gameId Game ID
     * @param int $categoryId Category ID
     * @param User $user Current user
     *
     * @return JsonResponse Success message
     */
    public function __invoke(
        int $gameId,
        int $categoryId,
        #[CurrentUser] User $user
    ): JsonResponse {
        try {
            $game = $this->gameRepository->find($gameId);

            if (!$game) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'GAME_NOT_FOUND',
                        'message' => 'Game not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            $category = $this->categoryRepository->find($categoryId);

            if (!$category) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'CATEGORY_NOT_FOUND',
                        'message' => 'Category not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            // Find existing vote
            $vote = $this->voteRepository->findVote($user, $game, $category);

            if (!$vote) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VOTE_NOT_FOUND',
                        'message' => 'You have not voted for this category',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            // Remove vote
            $this->entityManager->remove($vote);
            $this->entityManager->flush();

            // Get updated vote count
            $voteCount = $this->voteRepository->getVoteCount($game, $category);

            // Cleanup: Remove game-category association if vote count is too negative (community disagrees)
            // This threshold can be adjusted based on community size
            $removalThreshold = -5;
            if ($voteCount <= $removalThreshold) {
                $conn = $this->entityManager->getConnection();
                $deleteSql = 'DELETE FROM game_categories WHERE game_id = ? AND category_id = ?';
                $deleteStmt = $conn->prepare($deleteSql);
                $deleteStmt->executeStatement([$game->getId(), $category->getId()]);

                // Also remove all votes for this game-category pair since association is gone
                $deleteVotesSql = 'DELETE FROM game_category_votes WHERE game_id = ? AND category_id = ?';
                $deleteVotesStmt = $conn->prepare($deleteVotesSql);
                $deleteVotesStmt->executeStatement([$game->getId(), $category->getId()]);

                $voteCount = 0; // Reset since association is removed
            }

            return $this->json([
                'success' => true,
                'message' => 'Vote removed successfully',
                'data' => [
                    'voteCount' => $voteCount,
                    'userVoted' => false,
                ],
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNVOTE_FAILED',
                    'message' => 'Failed to remove vote',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
