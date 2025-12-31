<?php

namespace App\DTO\Request\User;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateThemeRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Theme name is required')]
        #[Assert\Length(max: 50, maxMessage: 'Theme name cannot exceed 50 characters')]
        public readonly string $theme
    ) {
    }
}
