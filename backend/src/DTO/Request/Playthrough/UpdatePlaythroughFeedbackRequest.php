<?php

namespace App\DTO\Request\Playthrough;

use Symfony\Component\Validator\Constraints as Assert;

class UpdatePlaythroughFeedbackRequest
{
    #[Assert\Type(type: 'boolean')]
    public ?bool $finishedRun = null;

    #[Assert\Type(type: 'integer')]
    #[Assert\Choice(choices: [-1, 0, 1], message: 'recommended must be -1, 0, or 1')]
    public ?int $recommended = null;
}
