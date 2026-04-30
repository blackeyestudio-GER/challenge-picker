<?php

namespace App\DTO\Response\Admin;

class DesignSetMutationResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly DesignSetMutationResponseData $data
    ) {
    }

    public static function fromValues(string $message, DesignSetListItem $designSet): self
    {
        return new self(
            success: true,
            data: new DesignSetMutationResponseData($message, $designSet)
        );
    }
}

class DesignSetMutationResponseData
{
    public function __construct(
        public readonly string $message,
        public readonly DesignSetListItem $designSet
    ) {
    }
}
