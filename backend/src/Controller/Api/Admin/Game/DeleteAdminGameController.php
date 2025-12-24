<?php

namespace App\Controller\Api\Admin\Game;

use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/games/{id}', name: 'api_admin_games_delete', methods: ['DELETE'])]
class DeleteAdminGameController extends AbstractController
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(int $id): JsonResponse
    {
        try {
            $game = $this->gameRepository->find($id);

            if (!$game) {
                return $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'GAME_NOT_FOUND',
                        'message' => 'Game not found',
                    ],
                ], Response::HTTP_NOT_FOUND);
            }

            // Soft delete: deactivate the game instead of removing it
            $game->setIsActive(false);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Game deactivated successfully',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            error_log('Failed to deactivate game: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'DEACTIVATE_FAILED',
                    'message' => 'Failed to deactivate game',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
