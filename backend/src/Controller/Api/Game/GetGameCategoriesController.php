<?php

namespace App\Controller\Api\Game;

use App\DTO\Response\Game\GameCategoryResponse;
use App\Repository\GameRepository;
use App\Repository\GameCategoryVoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

#[Route('/api/games/{id}/categories', name: 'api_game_categories', methods: ['GET'])]
class GetGameCategoriesController extends AbstractController
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly GameCategoryVoteRepository $voteRepository
    ) {}

    /**
     * Get all categories for a game with vote counts
     * 
     * @param int $id Game ID
     * @param User|null $user Current user (optional)
     * @return JsonResponse Categories with votes
     */
    public function __invoke(
        int $id,
        #[CurrentUser] ?User $user = null
    ): JsonResponse {
        try {
            $game = $this->gameRepository->find($id);
            
            if (!$game) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'GAME_NOT_FOUND',
                        'message' => 'Game not found'
                    ]
                ], Response::HTTP_NOT_FOUND);
            }

            $categoriesData = $this->voteRepository->getCategoriesWithVotes($game, $user);
            
            $categories = array_map(
                fn($data) => GameCategoryResponse::fromArray($data),
                $categoriesData
            );

            return $this->json([
                'success' => true,
                'data' => $categories
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FETCH_FAILED',
                    'message' => 'Failed to fetch game categories'
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

