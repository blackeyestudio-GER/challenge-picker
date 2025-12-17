<?php

namespace App\Constants;

/**
 * OBS Overlay Design Constants
 * 
 * These constants define the supported design variants for each overlay type.
 * Keep in sync with frontend: types/obs-designs.ts
 */
class ObsDesigns
{
    // Timer designs
    public const TIMER_DESIGNS = ['numbers'];
    public const DEFAULT_TIMER_DESIGN = 'numbers';

    // Status designs
    public const STATUS_DESIGNS = ['word', 'symbols', 'buttons'];
    public const DEFAULT_STATUS_DESIGN = 'word';

    // Rules designs
    public const RULES_DESIGNS = ['list'];
    public const DEFAULT_RULES_DESIGN = 'list';

    // Timer position (for rules overlay)
    public const TIMER_POSITIONS = ['none', 'on_card', 'below_card'];
    public const DEFAULT_TIMER_POSITION = 'none';

    // Validation helpers
    public static function isValidTimerDesign(string $design): bool
    {
        return in_array($design, self::TIMER_DESIGNS, true);
    }

    public static function isValidStatusDesign(string $design): bool
    {
        return in_array($design, self::STATUS_DESIGNS, true);
    }

    public static function isValidRulesDesign(string $design): bool
    {
        return in_array($design, self::RULES_DESIGNS, true);
    }

    public static function isValidTimerPosition(string $position): bool
    {
        return in_array($position, self::TIMER_POSITIONS, true);
    }
}

