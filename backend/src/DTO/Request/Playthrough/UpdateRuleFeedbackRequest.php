<?php

namespace App\DTO\Request\Playthrough;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateRuleFeedbackRequest
{
    #[Assert\NotBlank(message: 'Rule ID is required')]
    #[Assert\Type('integer')]
    #[Assert\Positive]
    public int $ruleId;

    #[Assert\NotBlank(message: 'couldBeHarder is required')]
    #[Assert\Type('boolean')]
    public bool $couldBeHarder;
}
