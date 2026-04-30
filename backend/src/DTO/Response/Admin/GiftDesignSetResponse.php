<?php

namespace App\DTO\Response\Admin;

class GiftDesignSetResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly GiftDesignSetResponseData $data
    ) {
    }

    public static function fromMessage(string $message): self
    {
        return new self(
            success: true,
            data: new GiftDesignSetResponseData($message)
        );
    }
}

class GiftDesignSetResponseData
{
    public function __construct(
        public readonly string $message
    ) {
    }
}
