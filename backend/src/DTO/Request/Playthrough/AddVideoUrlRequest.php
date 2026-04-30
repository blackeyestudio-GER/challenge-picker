<?php

namespace App\DTO\Request\Playthrough;

use Symfony\Component\Validator\Constraints as Assert;

class AddVideoUrlRequest
{
    #[Assert\Type(type: 'string')]
    public ?string $videoUrl = null;
}
