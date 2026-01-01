<?php

namespace App\DTO\Response\Playthrough;

use App\Entity\Playthrough;

class PlaythroughDetailsResponse
{
    public bool $success = true;
    public PlaythroughData $data;
}

class PlaythroughData
{
    public ?int $id;
    public ?string $uuid;
    public ?int $gameId;
    public ?string $gameName;
    public ?int $rulesetId;
    public ?string $rulesetName;
    public ?int $maxConcurrentRules;
    public ?string $status;

    /** @var array<PlaythroughRuleData> */
    public array $rules = [];

    public static function fromEntity(Playthrough $playthrough): self
    {
        $data = new self();
        $data->id = $playthrough->getId();
        $data->uuid = $playthrough->getUuid()?->toRfc4122();

        $game = $playthrough->getGame();
        $data->gameId = $game?->getId();
        $data->gameName = $game?->getName();

        $ruleset = $playthrough->getRuleset();
        $data->rulesetId = $ruleset?->getId();
        $data->rulesetName = $ruleset?->getName();

        $data->maxConcurrentRules = $playthrough->getMaxConcurrentRules();
        $data->status = $playthrough->getStatus();

        foreach ($playthrough->getPlaythroughRules() as $pr) {
            $ruleData = new PlaythroughRuleData();
            $ruleData->id = $pr->getId();

            $rule = $pr->getRule();
            $ruleData->ruleId = $rule?->getId();
            $ruleData->text = $rule?->getName();
            // Calculate duration from expiresAt and startedAt
            $durationSeconds = null;
            if ($pr->getExpiresAt() && $pr->getStartedAt()) {
                $durationSeconds = $pr->getExpiresAt()->getTimestamp() - $pr->getStartedAt()->getTimestamp();
            }
            $ruleData->durationSeconds = $durationSeconds;
            $ruleData->isActive = $pr->isActive();
            $ruleData->completed = $pr->getCompletedAt() !== null;

            $data->rules[] = $ruleData;
        }

        return $data;
    }
}

class PlaythroughRuleData
{
    public ?int $id;
    public ?int $ruleId;
    public ?string $text;
    public ?int $durationSeconds;
    public bool $isActive;
    public bool $completed;
}
