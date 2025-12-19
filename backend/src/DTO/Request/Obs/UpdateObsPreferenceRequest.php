<?php

namespace App\DTO\Request\Obs;

use App\Constants\ObsDesigns;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateObsPreferenceRequest
{
    #[Assert\Type('bool')]
    public ?bool $showTimerInSetup = null;

    #[Assert\Type('bool')]
    public ?bool $showTimerInActive = null;

    #[Assert\Type('bool')]
    public ?bool $showTimerInPaused = null;

    #[Assert\Type('bool')]
    public ?bool $showTimerInCompleted = null;

    #[Assert\Type('bool')]
    public ?bool $showStatusInSetup = null;

    #[Assert\Type('bool')]
    public ?bool $showStatusInActive = null;

    #[Assert\Type('bool')]
    public ?bool $showStatusInPaused = null;

    #[Assert\Type('bool')]
    public ?bool $showStatusInCompleted = null;

    #[Assert\Choice(choices: ObsDesigns::TIMER_POSITIONS)]
    public ?string $timerPosition = null;

    #[Assert\Choice(choices: ObsDesigns::TIMER_DESIGNS)]
    public ?string $timerDesign = null;

    #[Assert\Choice(choices: ObsDesigns::STATUS_DESIGNS)]
    public ?string $statusDesign = null;

    #[Assert\Choice(choices: ObsDesigns::RULES_DESIGNS)]
    public ?string $rulesDesign = null;

    #[Assert\Regex(
        pattern: '/^#[0-9A-Fa-f]{6}([0-9A-Fa-f]{2})?$/',
        message: 'Chroma key color must be in hex format: #RRGGBB or #RRGGBBAA'
    )]
    public ?string $chromaKeyColor = null;
}

