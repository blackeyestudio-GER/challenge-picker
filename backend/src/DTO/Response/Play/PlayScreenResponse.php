<?php

namespace App\DTO\Response\Play;

use App\Entity\Playthrough;

class PlayScreenResponse
{
    public bool $success = true;
    public PlayScreenData $data;

    public static function fromPlaythrough(Playthrough $playthrough): self
    {
        $response = new self();
        $response->data = PlayScreenData::fromEntity($playthrough);

        return $response;
    }
}

class PlayScreenData
{
    public ?int $id;
    public ?string $uuid;
    public ?string $userUuid;
    public ?string $gameName;
    public ?string $gameImage;
    public ?string $rulesetName;
    public ?string $gamehostUsername;
    public ?string $status;
    public ?int $maxConcurrentRules;
    public bool $requireAuth = false;
    public bool $allowViewerPicks = false;
    public ?string $startedAt;
    public ?int $totalDuration;

    /** @var array<ActiveRuleData> */
    public array $activeRules = [];

    public int $totalRulesCount;
    public int $activeRulesCount;
    public int $completedRulesCount;

    /** @var array<string, mixed> */
    public array $configuration = [];

    public static function fromEntity(Playthrough $playthrough): self
    {
        $data = new self();
        $data->id = $playthrough->getId();
        $data->uuid = $playthrough->getUuid()?->toRfc4122();

        $user = $playthrough->getUser();
        $data->userUuid = $user?->getUuid()->toRfc4122();
        $data->gamehostUsername = $user?->getUsername();

        $game = $playthrough->getGame();
        $data->gameName = $game?->getName();
        $data->gameImage = $game?->getImage();

        $ruleset = $playthrough->getRuleset();
        $data->rulesetName = $ruleset?->getName();

        $data->status = $playthrough->getStatus();
        $data->maxConcurrentRules = $playthrough->getMaxConcurrentRules();
        $data->requireAuth = $playthrough->isRequireAuth();
        $data->allowViewerPicks = $playthrough->isAllowViewerPicks();
        $data->startedAt = $playthrough->getStartedAt()?->format('c');
        $data->totalDuration = $playthrough->getTotalDuration();
        $data->configuration = $playthrough->getConfiguration();

        // Count rules
        $data->totalRulesCount = $playthrough->getPlaythroughRules()->count();
        $data->activeRulesCount = 0;
        $data->completedRulesCount = 0;

        foreach ($playthrough->getPlaythroughRules() as $pr) {
            if ($pr->isActive()) {
                ++$data->activeRulesCount;
            }
            if ($pr->getCompletedAt() !== null) {
                ++$data->completedRulesCount;
            }

            // For now, active rules list is empty (will be populated when session starts)
            // In future, this will include rules that are currently being displayed
        }

        return $data;
    }
}

class ActiveRuleData
{
    public int $id;
    public string $text;
    public int $durationSeconds;
    public ?string $startedAt;
}
