<?php

namespace App\DTO\Response\Rule;

use App\Entity\RuleDifficultyLevel;

class RuleDifficultyLevelResponse
{
    public ?int $difficultyLevel;
    public ?int $durationSeconds;
    public ?int $amount;
    public ?string $description;

    public static function fromEntity(RuleDifficultyLevel $level): self
    {
        $response = new self();
        $response->difficultyLevel = $level->getDifficultyLevel();
        $response->durationSeconds = $level->getDurationSeconds();
        $response->amount = $level->getAmount();
        $response->description = $level->getDescription();

        return $response;
    }
}
