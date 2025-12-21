<?php

namespace App\DTO\Response\Category;

use App\Entity\Category;

class CategoryResponse
{
    public int $id;
    public string $name;
    public ?string $description;
    public string $slug;
    public int $gameCount;

    public static function fromEntity(Category $category): self
    {
        $response = new self();
        $response->id = $category->getId();
        $response->name = $category->getName();
        $response->description = $category->getDescription();
        $response->slug = $category->getSlug();
        $response->gameCount = $category->getGames()->count();

        return $response;
    }
}

