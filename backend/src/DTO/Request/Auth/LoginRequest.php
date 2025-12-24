<?php

namespace App\DTO\Request\Auth;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Request DTO for user login.
 */
class LoginRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Email must be a valid email address')]
        public readonly string $email,
        #[Assert\NotBlank(message: 'Password is required')]
        public readonly string $password,
    ) {
    }
}
