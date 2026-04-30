<?php

namespace App\DTO\Response\Playthrough;

class BrowsePlaythroughResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly BrowsePlaythroughResponseData $data
    ) {
    }

    /**
     * @param list<BrowsePlaythroughItem> $playthroughs
     */
    public static function fromItems(array $playthroughs): self
    {
        return new self(
            success: true,
            data: new BrowsePlaythroughResponseData($playthroughs)
        );
    }
}

class BrowsePlaythroughResponseData
{
    /**
     * @param list<BrowsePlaythroughItem> $playthroughs
     */
    public function __construct(
        public readonly array $playthroughs
    ) {
    }
}

class BrowsePlaythroughItem
{
    public static function fromPlaythrough(
        PlaythroughResponse $playthrough,
        bool $isOwnRun,
        bool $hasPlayedGame
    ): self {
        return new self(
            id: $playthrough->id,
            uuid: $playthrough->uuid,
            userUuid: $playthrough->userUuid,
            username: $playthrough->username,
            gameId: $playthrough->gameId,
            gameName: $playthrough->gameName,
            rulesetId: $playthrough->rulesetId,
            rulesetName: $playthrough->rulesetName,
            maxConcurrentRules: $playthrough->maxConcurrentRules,
            status: $playthrough->status,
            startedAt: $playthrough->startedAt,
            endedAt: $playthrough->endedAt,
            pausedAt: $playthrough->pausedAt,
            totalPausedDuration: $playthrough->totalPausedDuration,
            totalDuration: $playthrough->totalDuration,
            videoUrl: $playthrough->videoUrl,
            finishedRun: $playthrough->finishedRun,
            recommended: $playthrough->recommended,
            configuration: $playthrough->configuration,
            createdAt: $playthrough->createdAt,
            isOwnRun: $isOwnRun,
            hasPlayedGame: $hasPlayedGame
        );
    }

    /**
     * @param array<string, mixed> $configuration
     */
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $uuid,
        public readonly ?string $userUuid,
        public readonly ?string $username,
        public readonly ?int $gameId,
        public readonly ?string $gameName,
        public readonly ?int $rulesetId,
        public readonly ?string $rulesetName,
        public readonly int $maxConcurrentRules,
        public readonly string $status,
        public readonly ?string $startedAt,
        public readonly ?string $endedAt,
        public readonly ?string $pausedAt,
        public readonly ?int $totalPausedDuration,
        public readonly ?int $totalDuration,
        public readonly ?string $videoUrl,
        public readonly ?bool $finishedRun,
        public readonly ?int $recommended,
        public readonly array $configuration,
        public readonly string $createdAt,
        public readonly bool $isOwnRun,
        public readonly bool $hasPlayedGame
    ) {
    }
}
