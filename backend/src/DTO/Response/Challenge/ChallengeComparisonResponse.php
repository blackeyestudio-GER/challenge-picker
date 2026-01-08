<?php

namespace App\DTO\Response\Challenge;

use App\Entity\Challenge;
use App\Entity\Playthrough;
use App\Entity\PlaythroughRule;

class ChallengeComparisonResponse
{
    public bool $success = true;
    public ChallengeComparisonData $data;
}

class ChallengeComparisonData
{
    public string $sourcePlaythroughUuid;
    public string $sourceUsername;
    public string $gameName;
    public string $rulesetName;
    public ?int $sourceDuration = null;
    public array $sourceActiveRules = [];
    
    /** @var array<ParticipantData> */
    public array $participants = [];
}

class ParticipantData
{
    public string $username;
    public string $playthroughUuid;
    public ?int $duration = null;
    public array $activeRules = [];
    public string $status; // 'accepted', 'pending', 'declined'
}

