<?php

namespace App\DTO\Response\Challenge;

class ChallengeListResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly ChallengeListResponseData $data
    ) {
    }

    /**
     * @param list<ChallengeItem> $challenges
     */
    public static function fromItems(array $challenges): self
    {
        return new self(
            success: true,
            data: new ChallengeListResponseData(
                challenges: $challenges,
                count: count($challenges)
            )
        );
    }
}

class ChallengeListResponseData
{
    /**
     * @param list<ChallengeItem> $challenges
     */
    public function __construct(
        public readonly array $challenges,
        public readonly int $count
    ) {
    }
}

class ChallengeItem
{
    public function __construct(
        public readonly string $uuid,
        public readonly ChallengeUserData $challenger,
        public readonly ChallengePlaythroughData $playthrough,
        public readonly string $createdAt,
        public readonly string $expiresAt
    ) {
    }
}

class ChallengeUserData
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $username,
        public readonly string $displayName
    ) {
    }
}

class ChallengePlaythroughData
{
    public function __construct(
        public readonly string $uuid,
        public readonly ChallengeRulesetData $ruleset,
        public readonly int $maxConcurrentRules
    ) {
    }
}

class ChallengeRulesetData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?ChallengeGameData $game
    ) {
    }
}

class ChallengeGameData
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $name,
        public readonly ?string $imageBase64
    ) {
    }
}
