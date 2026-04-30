<?php

namespace App\DTO\Response\Admin;

class DesignSetsResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly DesignSetsResponseData $data
    ) {
    }

    /**
     * @param list<DesignSetListItem> $designSets
     */
    public static function fromItems(array $designSets): self
    {
        return new self(
            success: true,
            data: new DesignSetsResponseData($designSets)
        );
    }
}

class DesignSetsResponseData
{
    /**
     * @param list<DesignSetListItem> $designSets
     */
    public function __construct(
        public readonly array $designSets
    ) {
    }
}

class DesignSetListItem
{
    /**
     * @param list<string> $previewImages
     */
    public function __construct(
        public readonly int $id,
        public readonly int $designNameId,
        public readonly string $designName,
        public readonly string $type,
        public readonly bool $isFree,
        public readonly bool $isPremium,
        public readonly ?string $price,
        public readonly ?string $theme,
        public readonly ?string $description,
        public readonly int $cardCount,
        public readonly int $completedCards,
        public readonly bool $isComplete,
        public readonly ?string $previewImage,
        public readonly array $previewImages,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {
    }
}
