<?php

namespace App\DTO\Response\Playthrough;

class ActivePlaythroughResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly ?PlaythroughResponse $data
    ) {
    }

    public static function fromPlaythrough(?PlaythroughResponse $playthrough): self
    {
        return new self(
            success: true,
            data: $playthrough
        );
    }
}
