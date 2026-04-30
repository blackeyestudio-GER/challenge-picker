<?php

namespace App\DTO\Response\Admin;

class DesignNameMutationResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly DesignNameMutationResponseData $data
    ) {
    }

    public static function fromValues(string $message, DesignNameItem $designName): self
    {
        return new self(
            success: true,
            data: new DesignNameMutationResponseData($message, $designName)
        );
    }
}

class DesignNameMutationResponseData
{
    public function __construct(
        public readonly string $message,
        public readonly DesignNameItem $designName
    ) {
    }
}
