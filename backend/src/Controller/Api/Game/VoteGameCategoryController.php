<?php

namespace App\Controller\Api\Game;

use App\Entity\GameCategoryVote;
use App\Repository\GameRepository;
use App\Repository\CategoryRepository;
use App\Repository\GameCategoryVoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

#[Route('/api/games/{gameId}/categories/{categoryId}/vote', name: 'api_game_category_vote', methods: ['POST'])]
class VoteGameCategoryController extends AbstractController
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly GameCategoryVoteRepository $voteRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    /**
     * Vote for a category to be associated with a game
     * 
     * @param int $gameId Game ID
     * @param int $categoryId Category ID
     * @param User $user Current user
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
                        'message' => 'Game not found'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            // Prevent voting on category representative games
            if ($game->isCategoryRepresentative()) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VOTING_NOT_ALLOWED',
                        'message' => 'This is a category representative game and cannot be voted on'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            $category = $this->categoryRepository->find($categoryId);
            
            if (!$category) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'CATEGORY_NOT_FOUND',
                        'message' => 'Category not found'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            // Check if user already voted
            $existingVote = $this->voteRepository->findVote($user, $game, $category);
            
            if ($existingVote) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'ALREADY_VOTED',
                        'message' => 'You have already voted for this category'
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            // Create new vote
            $vote = new GameCategoryVote();
            $vote->setUser($user);
            $vote->setGame($game);
            $vote->setCategory($category);

            $this->entityManager->persist($vote);
            $this->entityManager->flush();

            // Get updated vote count
            $voteCount = $this->voteRepository->getVoteCount($game, $category);

            return $this->json([
                'success' => true,
                'message' => 'Vote added successfully',
                'data' => [
                    'voteCount' => $voteCount,
                    'userVoted' => true
                ]
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'VOTE_FAILED',
                    'message' => 'Failed to add vote'
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

