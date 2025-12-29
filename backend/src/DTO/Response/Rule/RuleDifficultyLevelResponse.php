<?php

namespace App\DTO\Response\Rule;

use App\Entity\RuleDifficultyLevel;

class RuleDifficultyLevelResponse
{
    public ?int $difficultyLevel;
    public ?int $durationSeconds;
    public ?string $description;

    public static function fromEntity(RuleDifficultyLevel $level): self
    {
        $response = new self();
        $response->difficultyLevel = $level->getDifficultyLevel();
        $response->durationSeconds = $level->getDurationSeconds();
        $response->description = $level->getDescription();

        return $response;
    }
}
