<?php

namespace App\DTO\Response\Admin;

class FeatureSettingsResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly FeatureSettingsResponseData $data
    ) {
    }

    /**
     * @param list<FeatureSettingItem> $features
     */
    public static function fromItems(array $features): self
    {
        return new self(
            success: true,
            data: new FeatureSettingsResponseData($features)
        );
    }
}

class FeatureSettingsResponseData
{
    /**
     * @param list<FeatureSettingItem> $features
     */
    public function __construct(
        public readonly array $features
    ) {
    }
}

class FeatureSettingItem
{
    public function __construct(
        public readonly string $key,
        public readonly string $name,
        public readonly string $description,
        public readonly bool $enabled
    ) {
    }
}
