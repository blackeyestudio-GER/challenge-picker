<?php

namespace App\DTO\Response\Playthrough;

class AddVideoUrlResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly AddVideoUrlResponseData $data
    ) {
    }

    public static function fromValues(string $message, ?string $videoUrl): self
    {
        return new self(
            success: true,
            data: new AddVideoUrlResponseData(
                message: $message,
                videoUrl: $videoUrl
            )
        );
    }
}
