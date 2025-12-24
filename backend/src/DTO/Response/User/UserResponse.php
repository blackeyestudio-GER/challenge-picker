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
        public readonly ?string $discordId,
        public readonly ?string $discordUsername,
        public readonly ?string $discordAvatar,
        public readonly ?string $twitchId,
        public readonly ?string $twitchUsername,
        public readonly ?string $twitchAvatar,
    ) {
    }

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
            discordId: $user->getDiscordId(),
            discordUsername: $user->getDiscordUsername(),
            discordAvatar: $user->getDiscordAvatar(),
            twitchId: $user->getTwitchId(),
            twitchUsername: $user->getTwitchUsername(),
            twitchAvatar: $user->getTwitchAvatar(),
        );
    }
}
