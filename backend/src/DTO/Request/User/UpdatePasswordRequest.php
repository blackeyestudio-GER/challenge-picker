<?php

namespace App\DTO\Request\User;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Request DTO for updating user password
 */
class UpdatePasswordRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Current password is required')]
        public readonly string $currentPassword,

        #[Assert\NotBlank(message: 'New password is required')]
        #[Assert\Length(
            min: 8,
            minMessage: 'Password must be at least {{ limit }} characters'
        )]
        #[Assert\PasswordStrength(
            minScore: Assert\PasswordStrength::STRENGTH_MEDIUM,
            message: 'Password is too weak. Use a mix of letters, numbers, and symbols.'
        )]
        public readonly string $newPassword,
    ) {}
}

