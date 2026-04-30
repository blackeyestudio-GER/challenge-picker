<?php

namespace App\DTO\Request\Admin;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateShopSettingsRequest
{
    #[Assert\NotNull]
    #[Assert\Type(type: 'boolean')]
    public ?bool $shopEnabled = null;
}
