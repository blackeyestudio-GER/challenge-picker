<?php

namespace App\DTO\Request\Auth;

use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Token is required')]
        public readonly string $token,
        
        #[Assert\NotBlank(message: 'Password is required')]
        #[Assert\Length(
            min: 8,
            minMessage: 'Password must be at least {{ limit }} characters long'
        )]
        public readonly string $password
    ) {
    }
}

