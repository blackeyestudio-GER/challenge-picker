<?php

namespace App\DataFixtures;

/**
 * Category Images Data - Base64 encoded images for categories.
 *
 * Each category has a 256x256 placeholder icon
 * Icons are simple, professional colored badges
 *
 * To update: Replace base64 strings with actual category icons
 */
class CategoryImagesData
{
    /**
     * Returns array of category images [category_name => base64_data].
     *
     * @return array<string, string>
     */
    public static function getImages(): array
    {
        return [
            'Shooter' => 'data:image/svg+xml;base64,' . base64_encode(self::createCategoryIcon('Shooter', '#FF4444', '🎯')),
            'Battle Royale' => 'data:image/svg+xml;base64,' . base64_encode(self::createCategoryIcon('Battle Royale', '#FF6B35', '👑')),
            'MOBA' => 'data:image/svg+xml;base64,' . base64_encode(self::createCategoryIcon('MOBA', '#4ECDC4', '⚔️')),
            'RPG' => 'data:image/svg+xml;base64,' . base64_encode(self::createCategoryIcon('RPG', '#9B59B6', '🗡️')),
            'Horror' => 'data:image/svg+xml;base64,' . base64_encode(self::createCategoryIcon('Horror', '#2C3E50', '👻')),
            'Roguelike' => 'data:image/svg+xml;base64,' . base64_encode(self::createCategoryIcon('Roguelike', '#E74C3C', '🎲')),
            'Platformer' => 'data:image/svg+xml;base64,' . base64_encode(self::createCategoryIcon('Platformer', '#3498DB', '🏃')),
            'Fighting' => 'data:image/svg+xml;base64,' . base64_encode(self::createCategoryIcon('Fighting', '#E67E22', '🥊')),
            'Survival' => 'data:image/svg+xml;base64,' . base64_encode(self::createCategoryIcon('Survival', '#27AE60', '🏕️')),
            'Retro Shooter' => 'data:image/svg+xml;base64,' . base64_encode(self::createCategoryIcon('Retro', '#95A5A6', '👾')),
            'Strategy' => 'data:image/svg+xml;base64,' . base64_encode(self::createCategoryIcon('Strategy', '#16A085', '♟️')),
            'Action' => 'data:image/svg+xml;base64,' . base64_encode(self::createCategoryIcon('Action', '#F39C12', '💥')),
            'Adventure' => 'data:image/svg+xml;base64,' . base64_encode(self::createCategoryIcon('Adventure', '#1ABC9C', '🗺️')),
            'Action-Adventure' => 'data:image/svg+xml;base64,' . base64_encode(self::createCategoryIcon('Action-Adv', '#D35400', '⚡')),
            'Souls-like' => 'data:image/svg+xml;base64,' . base64_encode(self::createCategoryIcon('Souls-like', '#34495E', '🔥')),
            'Metroidvania' => 'data:image/svg+xml;base64,' . base64_encode(self::createCategoryIcon('Metroid', '#8E44AD', '🎮')),
            'Open World' => 'data:image/svg+xml;base64,' . base64_encode(self::createCategoryIcon('Open World', '#2ECC71', '🌍')),
        ];
    }

    /**
     * Creates a simple SVG category icon.
     */
    private static function createCategoryIcon(string $text, string $color, string $emoji): string
    {
        return <<<SVG
<svg width="256" height="256" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="grad-{$color}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:{$color};stop-opacity:1" />
      <stop offset="100%" style="stop-color:{$color};stop-opacity:0.7" />
    </linearGradient>
  </defs>
  
  <!-- Background -->
  <rect width="256" height="256" rx="32" fill="url(#grad-{$color})"/>
  
  <!-- Border -->
  <rect x="8" y="8" width="240" height="240" rx="28" fill="none" stroke="white" stroke-width="4" opacity="0.3"/>
  
  <!-- Emoji Icon -->
  <text x="128" y="140" font-size="80" text-anchor="middle" fill="white" opacity="0.9">{$emoji}</text>
  
  <!-- Category Text -->
  <text x="128" y="220" font-size="22" font-weight="bold" text-anchor="middle" fill="white" font-family="Arial, sans-serif">{$text}</text>
</svg>
SVG;
    }
}
