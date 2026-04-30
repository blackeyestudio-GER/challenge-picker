<?php

namespace App\DTO\Request\Playthrough;

use Symfony\Component\Validator\Constraints as Assert;

class PickRuleRequest
{
    #[Assert\NotNull]
    #[Assert\Type(type: 'integer')]
    public ?int $ruleId = null;

    #[Assert\NotNull]
    #[Assert\Type(type: 'integer')]
    public ?int $difficultyLevel = null;
}
