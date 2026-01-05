<?php

namespace App\DTO\Response\Rule;

use App\Entity\Rule;

class RuleResponse
{
    public ?int $id;
    public ?string $name;
    public ?string $description;
    public ?string $ruleType;
    public ?string $iconIdentifier;
    /** @var array<RuleDifficultyLevelResponse> */
    public array $difficultyLevels;

    public static function fromEntity(Rule $rule): self
    {
        $response = new self();
        $response->id = $rule->getId();
        $response->name = $rule->getName();
        $response->description = $rule->getDescription();
        $response->ruleType = $rule->getRuleType();
        $response->iconIdentifier = $rule->getIconIdentifier();

        $response->difficultyLevels = [];
        foreach ($rule->getDifficultyLevels() as $level) {
            $response->difficultyLevels[] = RuleDifficultyLevelResponse::fromEntity($level);
        }

        return $response;
    }
}
