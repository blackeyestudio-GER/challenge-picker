<?php

namespace App\Controller\Api\Game;

use App\DTO\Request\Game\UpdateGameRequest;
use App\DTO\Response\Game\GameResponse;
use App\Repository\GameRepository;
use App\Service\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/games/{id}', name: 'api_game_update', methods: ['PUT', 'PATCH'])]
class UpdateGameController extends AbstractController
{
    public function __construct(
        private readonly GameService $gameService,
        private readonly GameRepository $gameRepository
    ) {
    }

    public function __invoke(
        int $id,
        #[MapRequestPayload] UpdateGameRequest $request
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Check if user has admin role
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Admin access required',
                ],
            ], Response::HTTP_FORBIDDEN);
        }

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

        try {
            $updatedGame = $this->gameService->updateGame($game, $request);
            $gameResponse = GameResponse::fromEntity($updatedGame);

            return $this->json([
                'success' => true,
                'data' => $gameResponse,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'GAME_UPDATE_FAILED',
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
