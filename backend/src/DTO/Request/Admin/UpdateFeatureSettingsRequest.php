<?php

namespace App\DTO\Request\Admin;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateFeatureSettingsRequest
{
    #[Assert\NotNull]
    #[Assert\Choice(choices: ['browse_community_runs', 'shop'], message: 'Invalid feature key')]
    public ?string $featureKey = null;

    #[Assert\NotNull]
    #[Assert\Type(type: 'boolean')]
    public ?bool $enabled = null;
}
