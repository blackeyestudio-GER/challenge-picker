<?php

namespace App\DTO\Request\Challenge;

use Symfony\Component\Validator\Constraints as Assert;

class SendChallengeRequest
{
    #[Assert\NotBlank(message: 'Challenged user identifier is required')]
    #[Assert\Type('string')]
    public string $challengedUserIdentifier;

    #[Assert\NotBlank(message: 'Playthrough UUID is required')]
    #[Assert\Uuid(message: 'Invalid playthrough UUID format')]
    public string $playthroughUuid;
}
