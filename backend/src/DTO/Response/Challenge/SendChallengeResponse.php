<?php

namespace App\DTO\Response\Challenge;

class SendChallengeResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly SendChallengeResponseData $data
    ) {
    }

    public static function fromValues(string $challengeUuid, string $message): self
    {
        return new self(
            success: true,
            data: new SendChallengeResponseData(
                challengeUuid: $challengeUuid,
                message: $message
            )
        );
    }
}

class SendChallengeResponseData
{
    public function __construct(
        public readonly string $challengeUuid,
        public readonly string $message
    ) {
    }
}
