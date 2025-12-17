<?php

namespace App\DTO\Response\Auth;

use App\DTO\Response\User\UserResponse;

/**
 * Response DTO for successful login
 */
class LoginResponse
{
    public function __construct(
        public readonly string $token,
        public readonly UserResponse $user,
        public readonly int $expiresIn,
    ) {}
}

