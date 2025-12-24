<?php

namespace App\DTO\Response\Ruleset;

use App\Entity\Ruleset;

class RulesetResponse
{
    public ?int $id;
    public ?string $name;
    public ?string $description;
    public ?int $gameId;
    public ?string $gameName;
    public int $ruleCount;
    public bool $isFavorited = false;
    public int $voteCount = 0;
    public ?int $userVoteType = null;
    public bool $isInherited = false;
    public ?string $inheritedFromCategory = null;

    public static function fromEntity(
        Ruleset $ruleset,
        bool $isFavorited = false,
        int $voteCount = 0,
        ?int $userVoteType = null,
        bool $isInherited = false,
        ?string $inheritedFromCategory = null
    ): self {
        $response = new self();
        $response->id = $ruleset->getId();
        $response->name = $ruleset->getName();
        $response->description = $ruleset->getDescription();
        $game = $ruleset->getGame();
        $response->gameId = $game?->getId();
        $response->gameName = $game?->getName();
        $response->ruleCount = $ruleset->getRules()->count();
        $response->isFavorited = $isFavorited;
        $response->voteCount = $voteCount;
        $response->userVoteType = $userVoteType;
        $response->isInherited = $isInherited;
        $response->inheritedFromCategory = $inheritedFromCategory;

        return $response;
    }
}
