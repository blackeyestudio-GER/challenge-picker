<?php

namespace App\DTO\Response\Admin;

class AdminStatsResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly AdminStatsResponseData $data
    ) {
    }

    public static function fromValues(int $categories, int $games, int $rulesets, int $rules): self
    {
        return new self(
            success: true,
            data: new AdminStatsResponseData(
                categories: $categories,
                games: $games,
                rulesets: $rulesets,
                rules: $rules
            )
        );
    }
}

class AdminStatsResponseData
{
    public function __construct(
        public readonly int $categories,
        public readonly int $games,
        public readonly int $rulesets,
        public readonly int $rules
    ) {
    }
}
