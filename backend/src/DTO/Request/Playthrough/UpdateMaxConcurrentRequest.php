<?php

namespace App\DTO\Request\Playthrough;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateMaxConcurrentRequest
{
    #[Assert\NotBlank(message: 'Max concurrent rules is required')]
    #[Assert\Type('integer')]
    #[Assert\Range(min: 1, max: 10, notInRangeMessage: 'Max concurrent rules must be between {{ min }} and {{ max }}')]
    public int $maxConcurrentRules;
}
