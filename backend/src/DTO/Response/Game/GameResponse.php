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
    public array $categories = [];
    public bool $isCategoryRepresentative;
    public bool $isFavorited = false;
    public bool $isActive;
    public ?string $steamLink;
    public ?string $epicLink;
    public ?string $gogLink;
    public ?string $twitchCategory;

    public static function fromEntity(Game $game, bool $isFavorited = false): self
    {
        $response = new self();
        $response->id = $game->getId();
        $response->name = $game->getName();
        $response->description = $game->getDescription();
        $response->image = $game->getImage();
        $response->rulesetCount = $game->getRulesets()->count();
        $response->isCategoryRepresentative = $game->isCategoryRepresentative();
        $response->isFavorited = $isFavorited;
        $response->isActive = $game->isActive();
        
        // Add categories information
        $response->categories = [];
        foreach ($game->getCategories() as $category) {
            $response->categories[] = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'slug' => $category->getSlug(),
            ];
        }

        // Add store links
        $response->steamLink = $game->getSteamLink();
        $response->epicLink = $game->getEpicLink();
        $response->gogLink = $game->getGogLink();
        $response->twitchCategory = $game->getTwitchCategory();

        return $response;
    }
}

