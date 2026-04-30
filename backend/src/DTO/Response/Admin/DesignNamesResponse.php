<?php

namespace App\DTO\Response\Admin;

class DesignNamesResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly DesignNamesResponseData $data
    ) {
    }

    /**
     * @param list<DesignNameItem> $designNames
     */
    public static function fromItems(array $designNames): self
    {
        return new self(
            success: true,
            data: new DesignNamesResponseData($designNames)
        );
    }
}

class DesignNamesResponseData
{
    /**
     * @param list<DesignNameItem> $designNames
     */
    public function __construct(
        public readonly array $designNames
    ) {
    }
}

class DesignNameItem
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $createdAt,
        public readonly int $designSetCount
    ) {
    }
}
