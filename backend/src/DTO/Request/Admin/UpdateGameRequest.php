<?php

namespace App\DTO\Request\Admin;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateGameRequest
{
    #[Assert\Length(min: 1, max: 255)]
    public ?string $name = null;

    public ?string $description = null;

    public ?string $image = null;

    public ?bool $isCategoryRepresentative = null;
}

