<?php

namespace App\DTO\Response\Playthrough;

class UpdatePlaythroughPrivacyResponseData
{
    public function __construct(
        public readonly bool $requireAuth
    ) {
    }
}
