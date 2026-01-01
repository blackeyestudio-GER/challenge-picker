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
    public string $createdAt;

    public static function fromEntity(Playthrough $playthrough): self
    {
        $response = new self();
        $response->id = $playthrough->getId();
        $response->uuid = $playthrough->getUuid()->toRfc4122();

        $user = $playthrough->getUser();
        $response->userUuid = $user?->getUuid()->toRfc4122();
        $response->username = $user?->getUsername();

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
        $response->createdAt = $playthrough->getCreatedAt()->format('c');

        return $response;
    }
}
