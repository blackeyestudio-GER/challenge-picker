<?php

namespace App\DTO\Request\User;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Request DTO for creating a new user via email/password.
 */
class CreateUserRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Email must be a valid email address')]
        public readonly string $email,
        #[Assert\NotBlank(message: 'Username is required')]
        #[Assert\Length(
            min: 3,
            max: 50,
            minMessage: 'Username must be at least {{ limit }} characters',
            maxMessage: 'Username cannot be longer than {{ limit }} characters'
        )]
        #[Assert\Regex(
            pattern: '/^[a-zA-Z0-9_-]+$/',
            message: 'Username can only contain letters, numbers, underscores, and hyphens'
        )]
        public readonly string $username,
        #[Assert\NotBlank(message: 'Password is required')]
        #[Assert\Length(
            min: 8,
            minMessage: 'Password must be at least {{ limit }} characters'
        )]
        public readonly string $password,
    ) {
    }
}
