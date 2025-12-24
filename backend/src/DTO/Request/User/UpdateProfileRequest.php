<?php

namespace App\DTO\Request\User;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Request DTO for updating user profile.
 */
class UpdateProfileRequest
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
        public readonly ?string $avatar = null,
    ) {
    }
}
