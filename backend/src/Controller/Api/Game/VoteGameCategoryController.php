<?php

namespace App\Controller\Api\Game;

use App\Entity\GameCategoryVote;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\GameCategoryVoteRepository;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/games/{gameId}/categories/{categoryId}/vote', name: 'api_game_category_vote', methods: ['POST'])]
class VoteGameCategoryController extends AbstractController
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly GameCategoryVoteRepository $voteRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Vote for a category to be associated with a game
     * Supports upvotes (1) and downvotes (-1)
     * Clicking the same vote type again removes the vote.
     *
     * @param int $gameId Game ID
     * @param int $categoryId Category ID
     * @param Request $request Request containing voteType (1 or -1)
     * @param User $user Current user
     *
     * @return JsonResponse Success message
     */
    public function __invoke(
        int $gameId,
        int $categoryId,
        Request $request,
        #[CurrentUser] User $user
    ): JsonResponse {
        try {
            // Parse request body
            $data = json_decode($request->getContent(), true);
            $voteType = $data['voteType'] ?? 1; // Default to upvote

            // Validate vote type
            if (!in_array($voteType, [1, -1])) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_VOTE_TYPE',
                        'message' => 'Vote type must be 1 (upvote) or -1 (downvote)',
                    ],
                ], Response::HTTP_BAD_REQUEST);
            }

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

            // Prevent voting on category representative games
            if ($game->isCategoryRepresentative()) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VOTING_NOT_ALLOWED',
                        'message' => 'This is a category representative game and cannot be voted on',
                    ],
                ], Response::HTTP_BAD_REQUEST);
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

            // Check if user already voted
            $existingVote = $this->voteRepository->findVote($user, $game, $category);

            if ($existingVote) {
                // If clicking the same vote type, remove the vote
                if ($existingVote->getVoteType() === $voteType) {
                    $this->entityManager->remove($existingVote);
                    $this->entityManager->flush();

                    $voteCount = $this->voteRepository->getVoteCount($game, $category);

                    return $this->json([
                        'success' => true,
                        'message' => 'Vote removed successfully',
                        'data' => [
                            'voteCount' => $voteCount,
                            'userVoted' => false,
                            'userVoteType' => null,
                        ],
                    ], Response::HTTP_OK);
                } else {
                    // If clicking a different vote type, update the vote
                    $existingVote->setVoteType($voteType);
                    $this->entityManager->flush();

                    $voteCount = $this->voteRepository->getVoteCount($game, $category);

                    return $this->json([
                        'success' => true,
                        'message' => 'Vote updated successfully',
                        'data' => [
                            'voteCount' => $voteCount,
                            'userVoted' => true,
                            'userVoteType' => $voteType,
                        ],
                    ], Response::HTTP_OK);
                }
            }

            // Check if game-category association exists, create if it doesn't
            $conn = $this->entityManager->getConnection();
            $sql = 'SELECT id FROM game_categories WHERE game_id = ? AND category_id = ?';
            $stmt = $conn->prepare($sql);
            $result = $stmt->executeQuery([$game->getId(), $category->getId()]);

            if (!$result->fetchOne()) {
                // Create the association
                $insertSql = 'INSERT INTO game_categories (game_id, category_id) VALUES (?, ?)';
                $insertStmt = $conn->prepare($insertSql);
                $insertStmt->executeStatement([$game->getId(), $category->getId()]);
            }

            // Create new vote
            $vote = new GameCategoryVote();
            $vote->setUser($user);
            $vote->setGame($game);
            $vote->setCategory($category);
            $vote->setVoteType($voteType);

            $this->entityManager->persist($vote);
            $this->entityManager->flush();

            // Get updated vote count
            $voteCount = $this->voteRepository->getVoteCount($game, $category);

            return $this->json([
                'success' => true,
                'message' => 'Vote added successfully',
                'data' => [
                    'voteCount' => $voteCount,
                    'userVoted' => true,
                    'userVoteType' => $voteType,
                ],
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            error_log('Vote failed: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'VOTE_FAILED',
                    'message' => 'Failed to add vote',
                    'debug' => $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
