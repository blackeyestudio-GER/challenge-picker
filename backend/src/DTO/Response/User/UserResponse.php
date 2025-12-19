<?php

namespace App\DTO\Response\User;

use App\Entity\User;

class UserResponse
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $email,
        public readonly string $username,
        public readonly ?string $avatar,
        public readonly ?string $oauthProvider,
        public readonly bool $isAdmin,
        public readonly string $createdAt,
    ) {}

    public static function fromEntity(User $user): self
    {
        return new self(
            uuid: $user->getUuid(),
            email: $user->getEmail(),
            username: $user->getUsername(),
            avatar: $user->getAvatar(),
            oauthProvider: $user->getOauthProvider(),
            isAdmin: $user->isAdmin(),
            createdAt: $user->getCreatedAt()->format('c'), // ISO 8601 format
        );
    }
}

