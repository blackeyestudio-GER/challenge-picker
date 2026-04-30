<?php

namespace App\DTO\Response\Playthrough;

class AddVideoUrlResponseData
{
    public function __construct(
        public readonly string $message,
        public readonly ?string $videoUrl
    ) {
    }
}
