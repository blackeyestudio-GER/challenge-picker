<?php

namespace App\Service;

use App\DTO\Request\Game\CreateGameRequest;
use App\DTO\Request\Game\UpdateGameRequest;
use App\Entity\Game;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;

class GameService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GameRepository $gameRepository
    ) {
    }

    public function createGame(CreateGameRequest $request): Game
    {
        $game = new Game();
        $game->setName($request->name);
        
        if ($request->description) {
            $game->setDescription($request->description);
        }
        
        if ($request->image) {
            $game->setImage($request->image);
        }

        $this->entityManager->persist($game);
        $this->entityManager->flush();

        return $game;
    }

    public function updateGame(Game $game, UpdateGameRequest $request): Game
    {
        if ($request->name !== null) {
            $game->setName($request->name);
        }

        if ($request->description !== null) {
            $game->setDescription($request->description);
        }

        if ($request->image !== null) {
            $game->setImage($request->image);
        }

        $this->entityManager->flush();

        return $game;
    }

    public function deleteGame(Game $game): void
    {
        $this->entityManager->remove($game);
        $this->entityManager->flush();
    }
}

