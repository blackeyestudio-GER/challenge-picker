<?php

namespace App\Controller\Api\Game;

use App\DTO\Response\Game\GameListResponse;
use App\DTO\Response\Game\GameResponse;
use App\Entity\User;
use App\Repository\GameRepository;
use App\Repository\UserFavoriteGameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ListGamesController extends AbstractController
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly UserFavoriteGameRepository $favoriteRepository
    ) {
    }

    #[Route('/api/games', name: 'api_games_list', methods: ['GET'])]
    public function __invoke(#[CurrentUser] ?User $user = null): JsonResponse
    {
        $games = $this->gameRepository->findAllOrdered();

        // Get user's favorite game IDs if user is authenticated
        $favoriteGameIds = [];
        if ($user) {
            $favoriteGameIds = $this->favoriteRepository->getFavoriteGameIds($user);
        }

        $gameResponses = array_map(
            fn($game) => GameResponse::fromEntity($game, in_array($game->getId(), $favoriteGameIds)),
            $games
        );

        $response = GameListResponse::fromGames($gameResponses);

        return $this->json($response, Response::HTTP_OK);
    }
}

