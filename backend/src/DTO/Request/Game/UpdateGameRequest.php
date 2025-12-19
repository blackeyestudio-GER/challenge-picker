<?php

namespace App\DTO\Request\Game;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateGameRequest
{
    #[Assert\Length(min: 2, max: 255, minMessage: 'Game name must be at least {{ limit }} characters', maxMessage: 'Game name cannot exceed {{ limit }} characters')]
    public ?string $name = null;

    #[Assert\Length(max: 1000, maxMessage: 'Description cannot exceed {{ limit }} characters')]
    public ?string $description = null;

    #[Assert\Regex(
        pattern: '/^data:image\/(jpeg|jpg|png|gif|webp);base64,/',
        message: 'Image must be a valid base64 encoded image (jpeg, jpg, png, gif, or webp)'
    )]
    public ?string $image = null;
}

