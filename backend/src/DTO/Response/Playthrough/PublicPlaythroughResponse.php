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
        $activeRules = [];

        foreach ($playthrough->getPlaythroughRules() as $playthroughRule) {
            if (!$playthroughRule->isActive()) {
                continue;
            }

            $rule = $playthroughRule->getRule();
            if ($rule === null) {
                continue;
            }

            $ruleId = $rule->getId();
            $ruleName = $rule->getName();
            $ruleType = $rule->getRuleType();
            if ($ruleId === null || $ruleName === null || $ruleType === null) {
                continue;
            }

            $activeRules[] = new PublicPlaythroughRuleData(
                id: $ruleId,
                name: $ruleName,
                description: $rule->getDescription(),
                type: $ruleType
            );
        }

        return new self(
            uuid: $playthrough->getUuid()->toRfc4122(),
            status: $playthrough->getStatus(),
            startedAt: $playthrough->getStartedAt()?->format('c'),
            endedAt: $playthrough->getEndedAt()?->format('c'),
            totalDuration: $playthrough->getTotalDuration(),
            videoUrl: $playthrough->getVideoUrl(),
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
            activeRules: $activeRules
        );
    }

    /**
     * @param list<PublicPlaythroughRuleData> $activeRules
     */
    public function __construct(
        public readonly string $uuid,
        public readonly string $status,
        public readonly ?string $startedAt,
        public readonly ?string $endedAt,
        public readonly ?int $totalDuration,
        public readonly ?string $videoUrl,
        public readonly PublicPlaythroughGameData $game,
        public readonly PublicPlaythroughRulesetData $ruleset,
        public readonly PublicPlaythroughUserData $user,
        public readonly array $activeRules
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
        public readonly string $type
    ) {
    }
}
