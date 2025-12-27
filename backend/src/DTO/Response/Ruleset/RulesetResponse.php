<?php

namespace App\DTO\Response\Ruleset;

use App\Entity\Ruleset;

class RulesetResponse
{
    public ?int $id;
    public ?string $name;
    public ?string $description;
    /** @var array<array{id: int, name: string}> */
    public array $games;
    /** @var array<array{id: int, name: string, ruleType: string}> */
    public array $defaultRules;
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
        $response->games = $ruleset->getGames()
            ->filter(fn ($game) => $game->getId() !== null && $game->getName() !== null)
            ->map(function ($game) {
                $id = $game->getId();
                $name = $game->getName();
                assert($id !== null);
                assert($name !== null);

                return [
                    'id' => $id,
                    'name' => $name,
                ];
            })->toArray();
        // Get default rules from RulesetRuleCard entities where is_default = true
        $response->defaultRules = $ruleset->getRulesetRuleCards()
            ->filter(fn ($rulesetRuleCard) => $rulesetRuleCard->isDefault() && $rulesetRuleCard->getRule() !== null)
            ->map(function ($rulesetRuleCard) {
                $rule = $rulesetRuleCard->getRule();
                assert($rule !== null);
                $id = $rule->getId();
                $name = $rule->getName();
                $ruleType = $rule->getRuleType();
                assert($id !== null);
                assert($name !== null);
                assert($ruleType !== null);

                return [
                    'id' => $id,
                    'name' => $name,
                    'ruleType' => $ruleType,
                ];
            })->toArray();
        $response->ruleCount = $ruleset->getRulesetRuleCards()->count();
        $response->isFavorited = $isFavorited;
        $response->voteCount = $voteCount;
        $response->userVoteType = $userVoteType;
        $response->isInherited = $isInherited;
        $response->inheritedFromCategory = $inheritedFromCategory;

        return $response;
    }
}
