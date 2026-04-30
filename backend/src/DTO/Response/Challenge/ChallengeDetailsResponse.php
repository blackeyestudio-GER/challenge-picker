<?php

namespace App\DTO\Response\Challenge;

class ChallengeDetailsResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly ChallengeDetailsResponseData $data
    ) {
    }

    public static function fromData(ChallengeDetailsResponseData $data): self
    {
        return new self(
            success: true,
            data: $data
        );
    }
}

class ChallengeDetailsResponseData
{
    public function __construct(
        public readonly string $playthroughUuid,
        public readonly string $hostUsername,
        public readonly ChallengeDetailsGameData $game,
        public readonly ChallengeDetailsRulesetData $ruleset,
        public readonly int $maxConcurrentRules,
        public readonly bool $requireAuth,
        public readonly bool $allowViewerPicks
    ) {
    }
}

class ChallengeDetailsGameData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $imageBase64
    ) {
    }
}

class ChallengeDetailsRulesetData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $difficulty
    ) {
    }
}
