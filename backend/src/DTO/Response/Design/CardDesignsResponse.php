<?php

namespace App\DTO\Response\Design;

class CardDesignsResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly CardDesignsResponseData $data
    ) {
    }

    /**
     * @param array<string, CardDesignItem|null> $cardDesigns
     */
    public static function fromValues(?int $designSetId, string $designSetName, array $cardDesigns): self
    {
        return new self(
            success: true,
            data: new CardDesignsResponseData(
                designSetId: $designSetId,
                designSetName: $designSetName,
                cardDesigns: $cardDesigns
            )
        );
    }
}

class CardDesignsResponseData
{
    /**
     * @param array<string, CardDesignItem|null> $cardDesigns
     */
    public function __construct(
        public readonly ?int $designSetId,
        public readonly string $designSetName,
        public readonly array $cardDesigns
    ) {
    }
}

class CardDesignItem
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $cardIdentifier,
        public readonly ?string $imageBase64,
        public readonly bool $isTemplate,
        public readonly ?string $templateType
    ) {
    }
}
