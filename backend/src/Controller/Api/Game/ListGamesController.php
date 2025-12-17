<?php

namespace App\Controller\Api\Game;

use App\DTO\Response\Game\GameListResponse;
use App\DTO\Response\Game\GameResponse;
use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListGamesController extends AbstractController
{
    public function __construct(
        private readonly GameRepository $gameRepository
    ) {
    }

    #[Route('/api/games', name: 'api_games_list', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $games = $this->gameRepository->findAllOrdered();

        $gameResponses = array_map(
            fn($game) => GameResponse::fromEntity($game),
            $games
        );

        $response = GameListResponse::fromGames($gameResponses);

        return $this->json($response, Response::HTTP_OK);
    }
}

