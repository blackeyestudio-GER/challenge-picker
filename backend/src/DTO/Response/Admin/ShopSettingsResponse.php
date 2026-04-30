<?php

namespace App\DTO\Response\Admin;

class ShopSettingsResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly ShopSettingsResponseData $data
    ) {
    }

    public static function fromValues(bool $shopEnabled): self
    {
        return new self(
            success: true,
            data: new ShopSettingsResponseData($shopEnabled)
        );
    }
}

class ShopSettingsResponseData
{
    public function __construct(
        public readonly bool $shopEnabled
    ) {
    }
}
