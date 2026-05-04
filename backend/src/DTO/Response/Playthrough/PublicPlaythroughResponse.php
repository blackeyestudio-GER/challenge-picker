<?php

namespace App\DTO\Response\Playthrough;

use App\Entity\Playthrough;

class PublicPlaythroughResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly PublicPlaythroughResponseData $data
    ) {
    }

    public static function fromPlaythrough(Playthrough $playthrough): self
    {
        return new self(
            success: true,
            data: new PublicPlaythroughResponseData(
                playthrough: PublicPlaythroughData::fromEntity($playthrough)
            )
        );
    }
}

class PublicPlaythroughResponseData
{
    public function __construct(
        public readonly PublicPlaythroughData $playthrough
    ) {
    }
}

class PublicPlaythroughData
{
    public static function fromEntity(Playthrough $playthrough): self
    {
        $game = $playthrough->getGame();
        $ruleset = $playthrough->getRuleset();
        $user = $playthrough->getUser();
        $usedRules = self::buildUsedRules($playthrough);
        $ruleHistory = self::buildRuleHistory($playthrough);

        return new self(
            uuid: $playthrough->getUuid()->toRfc4122(),
            status: $playthrough->getStatus(),
            startedAt: $playthrough->getStartedAt()?->format('c'),
            endedAt: $playthrough->getEndedAt()?->format('c'),
            totalDuration: $playthrough->getTotalDuration(),
            videoUrl: $playthrough->getVideoUrl(),
            finishedRun: $playthrough->getFinishedRun(),
            recommended: $playthrough->getRecommended(),
            game: new PublicPlaythroughGameData(
                id: $game?->getId(),
                name: $game?->getName(),
                imageUrl: $game?->getImage()
            ),
            ruleset: new PublicPlaythroughRulesetData(
                id: $ruleset?->getId(),
                name: $ruleset?->getName(),
                description: $ruleset?->getDescription()
            ),
            user: new PublicPlaythroughUserData(
                username: $user->getUsername(),
                avatarUrl: $user->getAvatar()
            ),
            usedRules: $usedRules,
            ruleHistory: $ruleHistory
        );
    }

    /**
     * @return list<PublicPlaythroughRuleData>
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

            $rulesById[$ruleId] = new PublicPlaythroughRuleData(
                id: $ruleId,
                name: $ruleName,
                description: is_string($description) ? $description : null,
                type: is_string($ruleType) ? $ruleType : null
            );
        }

        return array_values($rulesById);
    }

    /**
     * @return list<PublicPlaythroughHistoryRuleData>
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

            $history[] = new PublicPlaythroughHistoryRuleData(
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
            static function (PublicPlaythroughHistoryRuleData $a, PublicPlaythroughHistoryRuleData $b): int {
                $aTimestamp = strtotime($a->startedAt ?? $a->createdAt ?? '') ?: 0;
                $bTimestamp = strtotime($b->startedAt ?? $b->createdAt ?? '') ?: 0;

                return $aTimestamp <=> $bTimestamp;
            }
        );

        return $history;
    }

    /**
     * @param list<PublicPlaythroughRuleData> $usedRules
     * @param list<PublicPlaythroughHistoryRuleData> $ruleHistory
     */
    public function __construct(
        public readonly string $uuid,
        public readonly string $status,
        public readonly ?string $startedAt,
        public readonly ?string $endedAt,
        public readonly ?int $totalDuration,
        public readonly ?string $videoUrl,
        public readonly ?bool $finishedRun,
        public readonly ?int $recommended,
        public readonly PublicPlaythroughGameData $game,
        public readonly PublicPlaythroughRulesetData $ruleset,
        public readonly PublicPlaythroughUserData $user,
        public readonly array $usedRules,
        public readonly array $ruleHistory
    ) {
    }
}

class PublicPlaythroughGameData
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $name,
        public readonly ?string $imageUrl
    ) {
    }
}

class PublicPlaythroughRulesetData
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $name,
        public readonly ?string $description
    ) {
    }
}

class PublicPlaythroughUserData
{
    public function __construct(
        public readonly ?string $username,
        public readonly ?string $avatarUrl
    ) {
    }
}

class PublicPlaythroughRuleData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $type
    ) {
    }
}

class PublicPlaythroughHistoryRuleData
{
    public function __construct(
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
