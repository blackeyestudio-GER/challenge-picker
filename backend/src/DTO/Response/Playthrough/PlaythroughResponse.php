<?php

namespace App\DTO\Response\Playthrough;

use App\Entity\Playthrough;

class PlaythroughResponse
{
    public ?int $id;
    public ?string $uuid;
    public ?string $userUuid;
    public ?string $username;
    public ?int $gameId;
    public ?string $gameName;
    public ?int $rulesetId;
    public ?string $rulesetName;
    public int $maxConcurrentRules;
    public string $status;
    public ?string $startedAt;
    public ?string $endedAt;
    public ?string $pausedAt;
    public ?int $totalPausedDuration;
    public ?int $totalDuration;
    public ?string $videoUrl;
    public ?bool $finishedRun;
    public ?int $recommended; // -1 = no, 0 = neutral, 1 = yes
    /** @var array<string, mixed> */
    public array $configuration; // JSON configuration snapshot (revision-safe)
    /** @var list<PlaythroughConfiguredRuleResponse> */
    public array $usedRules = [];
    /** @var list<PlaythroughRuleHistoryResponse> */
    public array $ruleHistory = [];
    public string $createdAt;

    public static function fromEntity(Playthrough $playthrough): self
    {
        $response = new self();
        $response->id = $playthrough->getId();
        $response->uuid = $playthrough->getUuid()->toRfc4122();

        $user = $playthrough->getUser();
        $response->userUuid = $user->getUuid()->toRfc4122();
        $response->username = $user->getUsername();

        $game = $playthrough->getGame();
        $response->gameId = $game?->getId();
        $response->gameName = $game?->getName();

        $ruleset = $playthrough->getRuleset();
        $response->rulesetId = $ruleset?->getId();
        $response->rulesetName = $ruleset?->getName();

        $response->maxConcurrentRules = $playthrough->getMaxConcurrentRules();
        $response->status = $playthrough->getStatus();
        $response->startedAt = $playthrough->getStartedAt()?->format('c');
        $response->endedAt = $playthrough->getEndedAt()?->format('c');
        $response->pausedAt = $playthrough->getPausedAt()?->format('c');
        $response->totalPausedDuration = $playthrough->getTotalPausedDuration();
        $response->totalDuration = $playthrough->getTotalDuration();
        $response->videoUrl = $playthrough->getVideoUrl();
        $response->finishedRun = $playthrough->getFinishedRun();
        $response->recommended = $playthrough->getRecommended();
        $response->configuration = $playthrough->getConfiguration();
        $response->usedRules = self::buildUsedRules($playthrough);
        $response->ruleHistory = self::buildRuleHistory($playthrough);
        $response->createdAt = $playthrough->getCreatedAt()->format('c');

        return $response;
    }

    /**
     * @return list<PlaythroughConfiguredRuleResponse>
     */
    private static function buildUsedRules(Playthrough $playthrough): array
    {
        $configuration = $playthrough->getConfiguration();
        $configuredRules = $configuration['rules'] ?? null;
        if (!is_array($configuredRules)) {
            return [];
        }

        $rulesById = [];

        foreach ($configuredRules as $ruleConfig) {
            if (!is_array($ruleConfig)) {
                continue;
            }

            $ruleId = $ruleConfig['ruleId'] ?? $ruleConfig['id'] ?? null;
            $ruleName = $ruleConfig['ruleName'] ?? $ruleConfig['name'] ?? null;
            if (!is_int($ruleId) || !is_string($ruleName) || $ruleName === '') {
                continue;
            }

            if (isset($rulesById[$ruleId])) {
                continue;
            }

            $description = $ruleConfig['ruleDescription'] ?? $ruleConfig['description'] ?? null;
            $ruleType = $ruleConfig['ruleType'] ?? null;
            $isDefault = $ruleConfig['isDefault'] ?? null;
            $isEnabled = $ruleConfig['isEnabled'] ?? $ruleConfig['enabled'] ?? null;

            $rulesById[$ruleId] = new PlaythroughConfiguredRuleResponse(
                id: $ruleId,
                name: $ruleName,
                description: is_string($description) ? $description : null,
                type: is_string($ruleType) ? $ruleType : null,
                isDefault: is_bool($isDefault) ? $isDefault : false,
                isEnabled: is_bool($isEnabled) ? $isEnabled : true
            );
        }

        return array_values($rulesById);
    }

    /**
     * @return list<PlaythroughRuleHistoryResponse>
     */
    private static function buildRuleHistory(Playthrough $playthrough): array
    {
        $history = [];

        foreach ($playthrough->getPlaythroughRules() as $playthroughRule) {
            $rule = $playthroughRule->getRule();
            if ($rule === null) {
                continue;
            }

            $ruleId = $rule->getId();
            $ruleName = $rule->getName();
            if ($ruleId === null || $ruleName === null) {
                continue;
            }

            if (
                $playthroughRule->getStartedAt() === null
                && $playthroughRule->getCompletedAt() === null
                && !$playthroughRule->isActive()
            ) {
                continue;
            }

            $history[] = new PlaythroughRuleHistoryResponse(
                id: $playthroughRule->getId(),
                ruleId: $ruleId,
                name: $ruleName,
                description: $rule->getDescription(),
                type: $rule->getRuleType(),
                isActive: (bool) $playthroughRule->isActive(),
                completed: $playthroughRule->getCompletedAt() !== null,
                currentAmount: $playthroughRule->getCurrentAmount(),
                startedAt: $playthroughRule->getStartedAt()?->format('c'),
                completedAt: $playthroughRule->getCompletedAt()?->format('c'),
                createdAt: $playthroughRule->getCreatedAt()?->format('c')
            );
        }

        usort(
            $history,
            static function (PlaythroughRuleHistoryResponse $a, PlaythroughRuleHistoryResponse $b): int {
                $aTimestamp = strtotime($a->startedAt ?? $a->createdAt ?? '') ?: 0;
                $bTimestamp = strtotime($b->startedAt ?? $b->createdAt ?? '') ?: 0;

                return $aTimestamp <=> $bTimestamp;
            }
        );

        return $history;
    }
}

class PlaythroughConfiguredRuleResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $type,
        public readonly bool $isDefault,
        public readonly bool $isEnabled
    ) {
    }
}

class PlaythroughRuleHistoryResponse
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $ruleId,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $type,
        public readonly bool $isActive,
        public readonly bool $completed,
        public readonly ?int $currentAmount,
        public readonly ?string $startedAt,
        public readonly ?string $completedAt,
        public readonly ?string $createdAt
    ) {
    }
}
