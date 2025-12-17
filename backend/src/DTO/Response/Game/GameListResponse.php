<?php

namespace App\DTO\Response\Game;

class GameListResponse
{
    public bool $success = true;

    /** @var GameResponse[] */
    public array $data = [];

    /**
     * @param GameResponse[] $games
     */
    public static function fromGames(array $games): self
    {
        $response = new self();
        $response->data = $games;

        return $response;
    }
}

