<?php

namespace App\DTO\Response\Challenge;

class ChallengeComparisonResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly ChallengeComparisonData $data
    ) {
    }

    public static function fromData(ChallengeComparisonData $data): self
    {
        return new self(
            success: true,
            data: $data
        );
    }
}

class ChallengeComparisonData
{
    public string $sourcePlaythroughUuid;
    public string $sourceUsername;
    public string $gameName;
    public string $rulesetName;
    public ?int $sourceDuration = null;
    /** @var list<array{ruleId: int|null, ruleName: string|null, ruleType: string|null, difficultyLevel: int|null, isActive: bool|null, completed: bool, currentAmount: int|null, startedAt: string|null, completedAt: string|null}> */
    public array $sourceActiveRules = [];

    /** @var array<ParticipantData> */
    public array $participants = [];
}

class ParticipantData
{
    public string $username;
    public string $playthroughUuid;
    public ?int $duration = null;
    /** @var list<array{ruleId: int|null, ruleName: string|null, ruleType: string|null, difficultyLevel: int|null, isActive: bool|null, completed: bool, currentAmount: int|null, startedAt: string|null, completedAt: string|null}> */
    public array $activeRules = [];
    public string $status; // 'accepted', 'pending', 'declined'
}
