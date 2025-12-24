<?php

namespace App\Enum;

enum TarotCardIdentifier: string
{
    // Major Arcana (22 cards)
    case THE_FOOL = 'The_Fool';
    case THE_MAGICIAN = 'The_Magician';
    case THE_HIGH_PRIESTESS = 'The_High_Priestess';
    case THE_EMPRESS = 'The_Empress';
    case THE_EMPEROR = 'The_Emperor';
    case THE_HIEROPHANT = 'The_Hierophant';
    case THE_LOVERS = 'The_Lovers';
    case THE_CHARIOT = 'The_Chariot';
    case STRENGTH = 'Strength';
    case THE_HERMIT = 'The_Hermit';
    case WHEEL_OF_FORTUNE = 'Wheel_Of_Fortune';
    case JUSTICE = 'Justice';
    case THE_HANGED_MAN = 'The_Hanged_Man';
    case DEATH = 'Death';
    case TEMPERANCE = 'Temperance';
    case THE_DEVIL = 'The_Devil';
    case THE_TOWER = 'The_Tower';
    case THE_STAR = 'The_Star';
    case THE_MOON = 'The_Moon';
    case THE_SUN = 'The_Sun';
    case JUDGEMENT = 'Judgement';
    case THE_WORLD = 'The_World';

    // Wands (14 cards)
    case WANDS_ACE = 'Wands_Ace';
    case WANDS_TWO = 'Wands_Two';
    case WANDS_THREE = 'Wands_Three';
    case WANDS_FOUR = 'Wands_Four';
    case WANDS_FIVE = 'Wands_Five';
    case WANDS_SIX = 'Wands_Six';
    case WANDS_SEVEN = 'Wands_Seven';
    case WANDS_EIGHT = 'Wands_Eight';
    case WANDS_NINE = 'Wands_Nine';
    case WANDS_TEN = 'Wands_Ten';
    case WANDS_PAGE = 'Wands_Page';
    case WANDS_KNIGHT = 'Wands_Knight';
    case WANDS_QUEEN = 'Wands_Queen';
    case WANDS_KING = 'Wands_King';

    // Cups (14 cards)
    case CUPS_ACE = 'Cups_Ace';
    case CUPS_TWO = 'Cups_Two';
    case CUPS_THREE = 'Cups_Three';
    case CUPS_FOUR = 'Cups_Four';
    case CUPS_FIVE = 'Cups_Five';
    case CUPS_SIX = 'Cups_Six';
    case CUPS_SEVEN = 'Cups_Seven';
    case CUPS_EIGHT = 'Cups_Eight';
    case CUPS_NINE = 'Cups_Nine';
    case CUPS_TEN = 'Cups_Ten';
    case CUPS_PAGE = 'Cups_Page';
    case CUPS_KNIGHT = 'Cups_Knight';
    case CUPS_QUEEN = 'Cups_Queen';
    case CUPS_KING = 'Cups_King';

    // Swords (14 cards)
    case SWORDS_ACE = 'Swords_Ace';
    case SWORDS_TWO = 'Swords_Two';
    case SWORDS_THREE = 'Swords_Three';
    case SWORDS_FOUR = 'Swords_Four';
    case SWORDS_FIVE = 'Swords_Five';
    case SWORDS_SIX = 'Swords_Six';
    case SWORDS_SEVEN = 'Swords_Seven';
    case SWORDS_EIGHT = 'Swords_Eight';
    case SWORDS_NINE = 'Swords_Nine';
    case SWORDS_TEN = 'Swords_Ten';
    case SWORDS_PAGE = 'Swords_Page';
    case SWORDS_KNIGHT = 'Swords_Knight';
    case SWORDS_QUEEN = 'Swords_Queen';
    case SWORDS_KING = 'Swords_King';

    // Pentacles (14 cards)
    case PENTACLES_ACE = 'Pentacles_Ace';
    case PENTACLES_TWO = 'Pentacles_Two';
    case PENTACLES_THREE = 'Pentacles_Three';
    case PENTACLES_FOUR = 'Pentacles_Four';
    case PENTACLES_FIVE = 'Pentacles_Five';
    case PENTACLES_SIX = 'Pentacles_Six';
    case PENTACLES_SEVEN = 'Pentacles_Seven';
    case PENTACLES_EIGHT = 'Pentacles_Eight';
    case PENTACLES_NINE = 'Pentacles_Nine';
    case PENTACLES_TEN = 'Pentacles_Ten';
    case PENTACLES_PAGE = 'Pentacles_Page';
    case PENTACLES_KNIGHT = 'Pentacles_Knight';
    case PENTACLES_QUEEN = 'Pentacles_Queen';
    case PENTACLES_KING = 'Pentacles_King';

    /**
     * Get display name (replace underscores with spaces)
     * Note: Display names are now stored in the tarot_cards table for better maintainability.
     */
    public function getDisplayName(): string
    {
        return str_replace('_', ' ', $this->value);
    }

    /**
     * Get all 78 card identifiers as array
     * Note: This is kept for validation purposes. All card data (rarity, sort order, etc.)
     * is now stored in the tarot_cards database table.
     */
    public static function getAllCards(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}
