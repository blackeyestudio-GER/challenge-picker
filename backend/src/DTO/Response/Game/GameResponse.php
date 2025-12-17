<?php

namespace App\DTO\Response\Game;

use App\Entity\Game;

class GameResponse
{
    public int $id;
    public string $name;
    public ?string $description;
    public ?string $image;
    public int $rulesetCount;

    public static function fromEntity(Game $game): self
    {
        $response = new self();
        $response->id = $game->getId();
        $response->name = $game->getName();
        $response->description = $game->getDescription();
        $response->image = $game->getImage();
        $response->rulesetCount = $game->getRulesets()->count();

        return $response;
    }
}

