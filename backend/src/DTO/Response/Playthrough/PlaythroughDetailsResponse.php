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
    public int $id;
    public string $uuid;
    public int $gameId;
    public string $gameName;
    public int $rulesetId;
    public string $rulesetName;
    public int $maxConcurrentRules;
    public string $status;

    /** @var PlaythroughRuleData[] */
    public array $rules = [];

    public static function fromEntity(Playthrough $playthrough): self
    {
        $data = new self();
        $data->id = $playthrough->getId();
        $data->uuid = $playthrough->getUuid();
        $data->gameId = $playthrough->getGame()->getId();
        $data->gameName = $playthrough->getGame()->getName();
        $data->rulesetId = $playthrough->getRuleset()->getId();
        $data->rulesetName = $playthrough->getRuleset()->getName();
        $data->maxConcurrentRules = $playthrough->getMaxConcurrentRules();
        $data->status = $playthrough->getStatus();

        foreach ($playthrough->getPlaythroughRules() as $pr) {
            $ruleData = new PlaythroughRuleData();
            $ruleData->id = $pr->getId();
            $ruleData->ruleId = $pr->getRule()->getId();
            $ruleData->text = $pr->getRule()->getText();
            $ruleData->durationMinutes = $pr->getRule()->getDurationMinutes();
            $ruleData->isActive = $pr->isActive();
            $ruleData->completed = $pr->getCompletedAt() !== null;

            $data->rules[] = $ruleData;
        }

        return $data;
    }
}

class PlaythroughRuleData
{
    public int $id;
    public int $ruleId;
    public string $text;
    public int $durationMinutes;
    public bool $isActive;
    public bool $completed;
}

