<?php

namespace App\DTO\Response\Admin;

class GameNamesResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly GameNamesResponseData $data
    ) {
    }

    /**
     * @param list<GameNameItem> $games
     */
    public static function fromItems(array $games): self
    {
        return new self(
            success: true,
            data: new GameNamesResponseData($games)
        );
    }
}

class GameNamesResponseData
{
    /**
     * @param list<GameNameItem> $games
     */
    public function __construct(
        public readonly array $games
    ) {
    }
}

class GameNameItem
{
    public function __construct(
        public readonly int $id,
        public readonly string $name
    ) {
    }
}
