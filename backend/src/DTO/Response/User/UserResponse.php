<?php

namespace App\DTO\Response\User;

use App\Entity\User;

class UserResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        public readonly string $username,
        public readonly ?string $avatar,
        public readonly ?string $oauthProvider,
        public readonly string $createdAt,
    ) {}

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId(),
            email: $user->getEmail(),
            username: $user->getUsername(),
            avatar: $user->getAvatar(),
            oauthProvider: $user->getOauthProvider(),
            createdAt: $user->getCreatedAt()->format('c'), // ISO 8601 format
        );
    }
}

