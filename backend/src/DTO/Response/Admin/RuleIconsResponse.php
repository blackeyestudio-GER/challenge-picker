<?php

namespace App\DTO\Response\Admin;

class RuleIconsResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly RuleIconsResponseData $data
    ) {
    }

    /**
     * @param list<RuleIconItem> $icons
     */
    public static function fromItems(array $icons): self
    {
        return new self(
            success: true,
            data: new RuleIconsResponseData($icons)
        );
    }
}

class RuleIconsResponseData
{
    /**
     * @param list<RuleIconItem> $icons
     */
    public function __construct(
        public readonly array $icons
    ) {
    }
}

class RuleIconItem
{
    /**
     * @param list<string>|null $tags
     */
    public function __construct(
        public readonly int $id,
        public readonly string $identifier,
        public readonly string $category,
        public readonly string $displayName,
        public readonly string $svgContent,
        public readonly ?array $tags,
        public readonly ?string $color,
        public readonly ?string $license,
        public readonly ?string $source,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {
    }
}
