<?php

namespace App\DTO\Response\Challenge;

class RespondToChallengeResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly RespondToChallengeResponseData $data
    ) {
    }

    public static function fromValues(string $message, ?string $playthroughUuid = null): self
    {
        return new self(
            success: true,
            data: new RespondToChallengeResponseData(
                message: $message,
                playthroughUuid: $playthroughUuid
            )
        );
    }
}

class RespondToChallengeResponseData
{
    public function __construct(
        public readonly string $message,
        public readonly ?string $playthroughUuid
    ) {
    }
}
