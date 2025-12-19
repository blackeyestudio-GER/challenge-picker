<?php

namespace App\Controller\Api\Game;

use App\DTO\Request\Game\CreateGameRequest;
use App\DTO\Response\Game\CreateGameResponse;
use App\DTO\Response\Game\GameResponse;
use App\Service\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/games', name: 'api_game_create', methods: ['POST'])]
class CreateGameController extends AbstractController
{
    public function __construct(
        private readonly GameService $gameService
    ) {
    }

    public function __invoke(
        #[MapRequestPayload] CreateGameRequest $request
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required'
                ]
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Check if user has admin role
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Admin access required'
                ]
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $game = $this->gameService->createGame($request);
            $gameResponse = GameResponse::fromEntity($game);
            $response = CreateGameResponse::fromGame($gameResponse);

            return $this->json($response, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => [
                    'code' => 'GAME_CREATION_FAILED',
                    'message' => $e->getMessage()
                ]
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}

