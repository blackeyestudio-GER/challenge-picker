<?php

namespace App\DTO\Response\Playthrough;

class PlaythroughActionResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly ?PlaythroughResponse $data,
        public readonly bool $deleted = false,
        public readonly ?string $message = null,
        public readonly ?string $uuid = null
    ) {
    }

    public static function fromPlaythrough(PlaythroughResponse $playthrough, ?string $message = null): self
    {
        return new self(
            success: true,
            data: $playthrough,
            message: $message,
            uuid: $playthrough->uuid
        );
    }

    public static function fromDeleted(string $uuid, string $message): self
    {
        return new self(
            success: true,
            data: null,
            deleted: true,
            message: $message,
            uuid: $uuid
        );
    }
}
