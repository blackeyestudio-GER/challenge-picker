<?php

namespace App\DTO\Response\Admin;

class CardDesignMutationResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly CardDesignMutationResponseData $data
    ) {
    }

    public static function fromValues(string $message, CardDesignMutationItem $cardDesign): self
    {
        return new self(
            success: true,
            data: new CardDesignMutationResponseData($message, $cardDesign)
        );
    }
}

class CardDesignMutationResponseData
{
    public function __construct(
        public readonly string $message,
        public readonly CardDesignMutationItem $cardDesign
    ) {
    }
}

class CardDesignMutationItem
{
    public function __construct(
        public readonly int $id,
        public readonly string $cardIdentifier,
        public readonly bool $hasImage,
        public readonly string $updatedAt
    ) {
    }
}
