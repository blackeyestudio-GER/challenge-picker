<?php

namespace App\Controller\Api\Game;

use App\Entity\User;
use App\Entity\UserFavoriteGame;
use App\Repository\GameRepository;
use App\Repository\UserFavoriteGameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/games/{gameId}/favorite', name: 'api_game_favorite', methods: ['POST'])]
class ToggleFavoriteGameController extends AbstractController
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly UserFavoriteGameRepository $favoriteRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(
        int $gameId,
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

            $existingFavorite = $this->favoriteRepository->findFavorite($user, $game);

            if ($existingFavorite) {
                // Remove favorite
                $this->entityManager->remove($existingFavorite);
                $this->entityManager->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Game removed from favorites',
                    'data' => [
                        'isFavorited' => false,
                    ],
                ], Response::HTTP_OK);
            } else {
                // Add favorite
                $favorite = new UserFavoriteGame();
                $favorite->setUser($user);
                $favorite->setGame($game);

                $this->entityManager->persist($favorite);
                $this->entityManager->flush();

                return $this->json([
                    'success' => true,
                    'message' => 'Game added to favorites',
                    'data' => [
                        'isFavorited' => true,
                    ],
                ], Response::HTTP_OK);
            }

        } catch (\Exception $e) {
            error_log('Toggle favorite failed: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FAVORITE_FAILED',
                    'message' => 'Failed to toggle favorite',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
