<?php

namespace App\DTO\Response\Challenge;

class SentChallengesResponse
{
    /**
     * @param list<SentChallengeGroup> $data
     */
    public function __construct(
        public readonly bool $success,
        public readonly array $data
    ) {
    }

    /**
     * @param list<SentChallengeGroup> $groups
     */
    public static function fromGroups(array $groups): self
    {
        return new self(
            success: true,
            data: $groups
        );
    }
}

class SentChallengeGroup
{
    /**
     * @param list<SentChallengeItem> $challenges
     */
    public function __construct(
        public readonly string $playthroughUuid,
        public readonly SentChallengeGameData $game,
        public readonly SentChallengeRulesetData $ruleset,
        public readonly string $createdAt,
        public readonly array $challenges
    ) {
    }
}

class SentChallengeGameData
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly ?string $imageBase64
    ) {
    }
}

class SentChallengeRulesetData
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name
    ) {
    }
}

class SentChallengeItem
{
    public function __construct(
        public readonly string $uuid,
        public readonly SentChallengeUserData $challengedUser,
        public readonly string $status,
        public readonly string $createdAt,
        public readonly ?string $respondedAt,
        public readonly string $expiresAt,
        public readonly ?string $resultingPlaythroughUuid
    ) {
    }
}

class SentChallengeUserData
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $username
    ) {
    }
}
