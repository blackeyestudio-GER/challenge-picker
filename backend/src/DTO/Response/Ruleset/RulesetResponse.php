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
    /** @var array<array{id: int, name: string, ruleType: string, isDefault: bool, difficultyLevels: array<array{difficultyLevel: int, durationSeconds: int|null, amount: int|null}>, description: string|null}> */
    public array $allRules;
    public int $ruleCount;
    public bool $isFavorited = false;
    public int $voteCount = 0;
    public ?int $userVoteType = null;
    public bool $isInherited = false;
    public ?string $inheritedFromCategory = null;
    public bool $isGameSpecific = true;
    public ?string $categoryName = null;
    public ?int $categoryId = null;

    public static function fromEntity(
        Ruleset $ruleset,
        bool $isFavorited = false,
        int $voteCount = 0,
        ?int $userVoteType = null,
        bool $isInherited = false,
        ?string $inheritedFromCategory = null,
        bool $isGameSpecific = true,
        ?string $categoryName = null,
        ?int $categoryId = null
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

        // Get all rules with difficulty levels
        $response->allRules = $ruleset->getRulesetRuleCards()
            ->filter(fn ($rulesetRuleCard) => $rulesetRuleCard->getRule() !== null)
            ->map(function ($rulesetRuleCard) {
                $rule = $rulesetRuleCard->getRule();
                assert($rule !== null);
                $id = $rule->getId();
                $name = $rule->getName();
                $ruleType = $rule->getRuleType();
                $description = $rule->getDescription();
                assert($id !== null);
                assert($name !== null);
                assert($ruleType !== null);

                // Get difficulty levels
                $difficultyLevels = $rule->getDifficultyLevels()
                    ->map(function ($level) {
                        return [
                            'difficultyLevel' => $level->getDifficultyLevel(),
                            'durationSeconds' => $level->getDurationSeconds(),
                            'amount' => $level->getAmount(),
                        ];
                    })->toArray();

                // Sort by difficulty level
                usort($difficultyLevels, fn ($a, $b) => $a['difficultyLevel'] <=> $b['difficultyLevel']);

                return [
                    'id' => $id,
                    'name' => $name,
                    'ruleType' => $ruleType,
                    'isDefault' => $rulesetRuleCard->isDefault(),
                    'difficultyLevels' => $difficultyLevels,
                    'description' => $description,
                ];
            })->toArray();

        $response->ruleCount = $ruleset->getRulesetRuleCards()->count();
        $response->isFavorited = $isFavorited;
        $response->voteCount = $voteCount;
        $response->userVoteType = $userVoteType;
        $response->isInherited = $isInherited;
        $response->inheritedFromCategory = $inheritedFromCategory;
        $response->isGameSpecific = $isGameSpecific;
        $response->categoryName = $categoryName;
        $response->categoryId = $categoryId;

        return $response;
    }
}
