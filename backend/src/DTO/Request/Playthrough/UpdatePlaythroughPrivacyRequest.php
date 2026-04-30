<?php

namespace App\DTO\Request\Playthrough;

use Symfony\Component\Validator\Constraints as Assert;

class UpdatePlaythroughPrivacyRequest
{
    #[Assert\NotNull]
    #[Assert\Type(type: 'boolean')]
    public ?bool $requireAuth = null;
}
