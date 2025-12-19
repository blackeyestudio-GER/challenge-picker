<?php

namespace App\DTO\Response\Game;

class CreateGameResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly GameResponse $data
    ) {
    }

    public static function fromGame(GameResponse $gameResponse): self
    {
        return new self(
            success: true,
            data: $gameResponse
        );
    }
}

