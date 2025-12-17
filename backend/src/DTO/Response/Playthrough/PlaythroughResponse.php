<?php

namespace App\DTO\Response\Playthrough;

use App\Entity\Playthrough;

class PlaythroughResponse
{
    public int $id;
    public string $uuid;
    public int $userId;
    public string $username;
    public int $gameId;
    public string $gameName;
    public int $rulesetId;
    public string $rulesetName;
    public int $maxConcurrentRules;
    public string $status;
    public ?string $startedAt;
    public ?string $endedAt;
    public ?int $totalDuration;
    public string $createdAt;

    public static function fromEntity(Playthrough $playthrough): self
    {
        $response = new self();
        $response->id = $playthrough->getId();
        $response->uuid = $playthrough->getUuid();
        $response->userId = $playthrough->getUser()->getId();
        $response->username = $playthrough->getUser()->getUsername();
        $response->gameId = $playthrough->getGame()->getId();
        $response->gameName = $playthrough->getGame()->getName();
        $response->rulesetId = $playthrough->getRuleset()->getId();
        $response->rulesetName = $playthrough->getRuleset()->getName();
        $response->maxConcurrentRules = $playthrough->getMaxConcurrentRules();
        $response->status = $playthrough->getStatus();
        $response->startedAt = $playthrough->getStartedAt()?->format('c');
        $response->endedAt = $playthrough->getEndedAt()?->format('c');
        $response->totalDuration = $playthrough->getTotalDuration();
        $response->createdAt = $playthrough->getCreatedAt()->format('c');

        return $response;
    }
}

