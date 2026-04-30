<?php

namespace App\DTO\Response\Admin;

use App\DTO\Response\Category\CategoryResponse;

class CategoryMutationResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly CategoryMutationResponseData $data
    ) {
    }

    public static function fromValues(string $message, CategoryResponse $category): self
    {
        return new self(
            success: true,
            data: new CategoryMutationResponseData($message, $category)
        );
    }
}

class CategoryMutationResponseData
{
    public function __construct(
        public readonly string $message,
        public readonly CategoryResponse $category
    ) {
    }
}
