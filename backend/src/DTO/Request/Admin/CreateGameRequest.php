<?php

namespace App\DTO\Request\Admin;

use Symfony\Component\Validator\Constraints as Assert;

class CreateGameRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    public string $name;

    public ?string $description = null;

    public ?string $image = null;

    public bool $isCategoryRepresentative = false;
}
