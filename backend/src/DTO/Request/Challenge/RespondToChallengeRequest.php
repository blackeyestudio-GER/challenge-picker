<?php

namespace App\DTO\Request\Challenge;

use Symfony\Component\Validator\Constraints as Assert;

class RespondToChallengeRequest
{
    #[Assert\NotNull]
    #[Assert\Choice(choices: ['accept', 'decline'], message: 'Action must be either "accept" or "decline"')]
    public ?string $action = null;
}
