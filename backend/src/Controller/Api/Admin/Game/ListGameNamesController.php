<?php

namespace App\Controller\Api\Admin\Game;

use App\DTO\Response\Admin\GameNameItem;
use App\DTO\Response\Admin\GameNamesResponse;
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
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $games = $this->gameRepository->findAllOrdered();

        $gameNames = array_filter(array_map(
            fn ($game) => ($game->getId() !== null && $game->getName() !== null)
                ? new GameNameItem($game->getId(), $game->getName())
                : null,
            $games
        ));

        return $this->json(GameNamesResponse::fromItems(array_values($gameNames)), Response::HTTP_OK);
    }
}
