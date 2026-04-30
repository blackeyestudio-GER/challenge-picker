<?php

namespace App\DTO\Response\Playthrough;

class UpdatePlaythroughPrivacyResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly UpdatePlaythroughPrivacyResponseData $data
    ) {
    }

    public static function fromValue(bool $requireAuth): self
    {
        return new self(
            success: true,
            data: new UpdatePlaythroughPrivacyResponseData(
                requireAuth: $requireAuth
            )
        );
    }
}
