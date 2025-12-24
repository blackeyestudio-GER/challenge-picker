<?php

namespace App\DTO\Response\Category;

use App\Entity\Category;

class CategoryResponse
{
    public ?int $id;
    public ?string $name;
    public ?string $description;
    public ?string $slug;
    public int $gameCount;
    /** @var array<array{id: ?int, name: ?string, image: ?string, isActive: bool, isCategoryRepresentative: bool}> */
    public array $games = [];

    public static function fromEntity(Category $category): self
    {
        $response = new self();
        $response->id = $category->getId();
        $response->name = $category->getName();
        $response->description = $category->getDescription();
        $response->slug = $category->getSlug();
        $response->gameCount = $category->getGames()->count();

        // Add games information
        $response->games = [];
        foreach ($category->getGames() as $game) {
            $response->games[] = [
                'id' => $game->getId(),
                'name' => $game->getName(),
                'image' => $game->getImage(),
                'isActive' => $game->isActive(),
                'isCategoryRepresentative' => $game->isCategoryRepresentative(),
            ];
        }

        return $response;
    }
}
