<?php

namespace App\DTO\Response\Admin;

class UpdateShopSettingsResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly UpdateShopSettingsResponseData $data
    ) {
    }

    public static function fromValues(string $message, bool $shopEnabled): self
    {
        return new self(
            success: true,
            data: new UpdateShopSettingsResponseData(
                message: $message,
                shopEnabled: $shopEnabled
            )
        );
    }
}

class UpdateShopSettingsResponseData
{
    public function __construct(
        public readonly string $message,
        public readonly bool $shopEnabled
    ) {
    }
}
