<?php

namespace App\DTO\Response\Admin;

use App\DTO\Response\Game\GameResponse;

class GameMutationResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly GameMutationResponseData $data
    ) {
    }

    public static function fromValues(string $message, GameResponse $game): self
    {
        return new self(
            success: true,
            data: new GameMutationResponseData($message, $game)
        );
    }
}

class GameMutationResponseData
{
    public function __construct(
        public readonly string $message,
        public readonly GameResponse $game
    ) {
    }
}
