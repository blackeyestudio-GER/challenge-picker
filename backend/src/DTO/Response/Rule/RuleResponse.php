<?php

namespace App\DTO\Response\Rule;

use App\Entity\Rule;

class RuleResponse
{
    public int $id;
    public string $name;
    public ?string $description;
    public int $durationMinutes;
    /** @var array<array{id: int, name: string}> */
    public array $rulesets;

    public static function fromEntity(Rule $rule): self
    {
        $response = new self();
        $response->id = $rule->getId();
        $response->name = $rule->getName();
        $response->description = $rule->getDescription();
        $response->durationMinutes = $rule->getDurationMinutes();
        
        $response->rulesets = [];
        foreach ($rule->getRulesets() as $ruleset) {
            $response->rulesets[] = [
                'id' => $ruleset->getId(),
                'name' => $ruleset->getName()
            ];
        }

        return $response;
    }
}

