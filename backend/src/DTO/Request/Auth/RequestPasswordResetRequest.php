<?php

namespace App\DTO\Request\Auth;

use Symfony\Component\Validator\Constraints as Assert;

class RequestPasswordResetRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Email must be a valid email address')]
        public readonly string $email
    ) {
    }
}

