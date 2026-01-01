<?php

namespace App\DTO\Request\Playthrough;

use Symfony\Component\Validator\Constraints as Assert;

class CreatePlaythroughRequest
{
    #[Assert\NotBlank(message: 'Game ID is required')]
    #[Assert\Type('integer')]
    #[Assert\Positive]
    public int $gameId;

    #[Assert\NotBlank(message: 'Ruleset ID is required')]
    #[Assert\Type('integer')]
    #[Assert\Positive]
    public int $rulesetId;

    #[Assert\NotBlank(message: 'Max concurrent rules is required')]
    #[Assert\Type('integer')]
    #[Assert\Range(min: 1, max: 10, notInRangeMessage: 'Max concurrent rules must be between {{ min }} and {{ max }}')]
    public int $maxConcurrentRules = 3;

    /** @var array<string, mixed>|null */
    public ?array $configuration = null; // Optional: JSON configuration snapshot (revision-safe). If not provided, will be auto-generated.
}
