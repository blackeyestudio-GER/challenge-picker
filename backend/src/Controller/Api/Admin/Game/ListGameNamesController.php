<?php

namespace App\Controller\Api\Admin\Game;

use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/games/names', name: 'api_admin_games_names', methods: ['GET'])]
class ListGameNamesController extends AbstractController
{
    public function __construct(
        private readonly GameRepository $gameRepository
    ) {}

    public function __invoke(): JsonResponse
    {
        $games = $this->gameRepository->findAllOrdered();

        $gameNames = array_map(
            fn($game) => [
                'id' => $game->getId(),
                'name' => $game->getName()
            ],
            $games
        );

        return $this->json([
            'success' => true,
            'data' => ['games' => $gameNames]
        ], Response::HTTP_OK);
    }
}

