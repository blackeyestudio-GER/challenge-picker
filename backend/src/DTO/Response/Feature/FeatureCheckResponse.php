<?php

namespace App\DTO\Response\Feature;

class FeatureCheckResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly FeatureCheckResponseData $data
    ) {
    }

    public static function fromValues(string $feature, bool $enabled): self
    {
        return new self(
            success: true,
            data: new FeatureCheckResponseData(
                feature: $feature,
                enabled: $enabled
            )
        );
    }
}

class FeatureCheckResponseData
{
    public function __construct(
        public readonly string $feature,
        public readonly bool $enabled
    ) {
    }
}
