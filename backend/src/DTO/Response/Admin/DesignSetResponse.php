<?php

namespace App\DTO\Response\Admin;

class DesignSetResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly DesignSetResponseData $data
    ) {
    }

    public static function fromItem(DesignSetDetailItem $designSet): self
    {
        return new self(
            success: true,
            data: new DesignSetResponseData($designSet)
        );
    }
}

class DesignSetResponseData
{
    public function __construct(
        public readonly DesignSetDetailItem $designSet
    ) {
    }
}

class DesignSetDetailItem
{
    /**
     * @param list<DesignSetCardItem> $cards
     */
    public function __construct(
        public readonly int $id,
        public readonly int $designNameId,
        public readonly string $designName,
        public readonly string $type,
        public readonly bool $isPremium,
        public readonly ?string $price,
        public readonly ?string $theme,
        public readonly ?string $description,
        public readonly int $cardCount,
        public readonly int $expectedCardCount,
        public readonly int $completedCards,
        public readonly bool $isComplete,
        public readonly array $cards,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {
    }
}

class DesignSetCardItem
{
    public function __construct(
        public readonly int $id,
        public readonly string $cardIdentifier,
        public readonly string $displayName,
        public readonly ?string $imageBase64,
        public readonly bool $hasImage,
        public readonly bool $isTemplate,
        public readonly ?string $templateType,
        public readonly bool $requiresIconComposition,
        public readonly string $rarity,
        public readonly string $updatedAt
    ) {
    }
}
