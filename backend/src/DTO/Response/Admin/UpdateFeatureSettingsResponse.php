<?php

namespace App\DTO\Response\Admin;

class UpdateFeatureSettingsResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly UpdateFeatureSettingsResponseData $data
    ) {
    }

    public static function fromValues(string $key, bool $enabled): self
    {
        return new self(
            success: true,
            data: new UpdateFeatureSettingsResponseData(
                feature: new UpdateFeatureSettingsFeatureData(
                    key: $key,
                    enabled: $enabled
                )
            )
        );
    }
}

class UpdateFeatureSettingsResponseData
{
    public function __construct(
        public readonly UpdateFeatureSettingsFeatureData $feature
    ) {
    }
}

class UpdateFeatureSettingsFeatureData
{
    public function __construct(
        public readonly string $key,
        public readonly bool $enabled
    ) {
    }
}
