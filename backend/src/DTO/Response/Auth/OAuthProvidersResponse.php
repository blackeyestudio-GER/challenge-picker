<?php

namespace App\DTO\Response\Auth;

class OAuthProvidersResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly OAuthProvidersResponseData $data
    ) {
    }

    public static function fromValues(bool $twitchAccountLinking): self
    {
        return new self(
            success: true,
            data: new OAuthProvidersResponseData(
                twitchAccountLinking: $twitchAccountLinking
            )
        );
    }
}

class OAuthProvidersResponseData
{
    public function __construct(
        public readonly bool $twitchAccountLinking
    ) {
    }
}
