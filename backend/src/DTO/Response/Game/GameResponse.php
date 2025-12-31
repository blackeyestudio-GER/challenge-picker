<?php

namespace App\DTO\Response\Game;

use App\Entity\Game;
use App\Repository\RulesetRepository;

class GameResponse
{
    public ?int $id;
    public ?string $name;
    public ?string $description;
    public ?string $image;
    public int $rulesetCount;
    public int $gameSpecificRulesetCount;
    public int $categoryBasedRulesetCount;
    /** @var array<array{id: ?int, name: ?string, slug: ?string}> */
    public array $categories = [];
    public bool $isCategoryRepresentative;
    public bool $isFavorited = false;
    public bool $isActive;
    public ?string $steamLink;
    public ?string $epicLink;
    public ?string $gogLink;
    public ?string $twitchCategory;

    public static function fromEntity(Game $game, bool $isFavorited = false, ?RulesetRepository $rulesetRepository = null): self
    {
        $response = new self();
        $response->id = $game->getId();
        $response->name = $game->getName();
        $response->description = $game->getDescription();
        $response->image = $game->getImage();

        // Calculate ruleset counts
        $gameId = $game->getId();
        if ($gameId && $rulesetRepository) {
            $rulesetsWithMetadata = $rulesetRepository->findByGameWithMetadata($gameId);
            $response->gameSpecificRulesetCount = count(array_filter($rulesetsWithMetadata, fn ($r) => $r['isGameSpecific']));
            $response->categoryBasedRulesetCount = count(array_filter($rulesetsWithMetadata, fn ($r) => !$r['isGameSpecific']));
            $response->rulesetCount = count($rulesetsWithMetadata);
        } else {
            // Fallback to direct count if repository not provided
            $response->rulesetCount = $game->getRulesets()->count();
            $response->gameSpecificRulesetCount = $response->rulesetCount;
            $response->categoryBasedRulesetCount = 0;
        }

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
