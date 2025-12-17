<?php

namespace App\DTO\Response\Ruleset;

use App\Entity\Ruleset;

class RulesetResponse
{
    public int $id;
    public string $name;
    public ?string $description;
    public int $gameId;
    public string $gameName;
    public int $ruleCount;

    public static function fromEntity(Ruleset $ruleset): self
    {
        $response = new self();
        $response->id = $ruleset->getId();
        $response->name = $ruleset->getName();
        $response->description = $ruleset->getDescription();
        $response->gameId = $ruleset->getGame()->getId();
        $response->gameName = $ruleset->getGame()->getName();
        $response->ruleCount = $ruleset->getRules()->count();

        return $response;
    }
}

