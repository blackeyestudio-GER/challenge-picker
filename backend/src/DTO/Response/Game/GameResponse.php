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
    public ?int $categoryId;
    public ?string $categoryName;
    public ?string $categorySlug;
    public bool $isCategoryRepresentative;

    public static function fromEntity(Game $game): self
    {
        $response = new self();
        $response->id = $game->getId();
        $response->name = $game->getName();
        $response->description = $game->getDescription();
        $response->image = $game->getImage();
        $response->rulesetCount = $game->getRulesets()->count();
        $response->isCategoryRepresentative = $game->isCategoryRepresentative();
        
        // Add category information
        $category = $game->getCategory();
        $response->categoryId = $category?->getId();
        $response->categoryName = $category?->getName();
        $response->categorySlug = $category?->getSlug();

        return $response;
    }
}

