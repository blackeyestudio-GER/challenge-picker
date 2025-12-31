<?php

namespace App\DataFixtures;

use App\Entity\RuleIcon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RuleIconFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            TarotCardFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $icons = $this->getIconsData();

        foreach ($icons as $iconData) {
            $icon = new RuleIcon();
            $icon->setIdentifier($iconData['identifier']);
            $icon->setCategory($iconData['category']);
            $icon->setDisplayName($iconData['displayName']);
            $icon->setSvgContent($iconData['svg']);
            $icon->setTags($iconData['tags']);
            $icon->setColor($iconData['color'] ?? null);
            $icon->setLicense('CC BY 4.0 / SIL OFL 1.1 / MIT');
            $icon->setSource('Font Awesome (fontawesome.com)');

            $manager->persist($icon);

            // Create reference for linking to rules
            $this->addReference('icon_' . $iconData['identifier'], $icon);
        }

        $manager->flush();

        echo sprintf("\n✓ Created %d rule icons\n", count($icons));
    }

    /**
     * Get basic icon data.
     * SVGs are simplified versions for demonstration.
     */
    private function getIconsData(): array
    {
        return [
            // ===== WEAPONS =====
            [
                'identifier' => 'knife',
                'category' => 'weapon',
                'displayName' => 'Knife',
                'tags' => ['weapon', 'melee', 'blade'],
                'color' => '#8B9DC3',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M89.9 115.4l90.5 90.5L339 47.3C361.6 24.6 392.9 13.6 425 16.1s62.1 18.9 80.5 45.9l62.6 91.3c6.5 9.5 4.5 22.5-4.7 29.4l-46.7 34.9c-8.6 6.4-20.8 5.5-28.3-2l-50.5-50.5c-6.2-6.2-16.4-6.2-22.6 0s-6.2 16.4 0 22.6l50.5 50.5c7.5 7.5 8.4 19.7 2 28.3l-34.9 46.7c-6.9 9.2-19.9 11.2-29.4 4.7l-91.3-62.6c-27-18.4-41.8-50.4-45.9-80.5s-8.5-63.4 14.1-86L247.6 28.3c-9.5-6.5-11.5-19.5-4.7-29.4C261.8-28.1 295.9-5.3 320 28.3l46.7 62.6c6.9 9.2 19.9 11.2 29.4 4.7l46.7-34.9c9.2-6.9 11.2-19.9 4.7-29.4L401.8 10C373.8-22.7 333.7-40.6 290.6-37.2s-82 25.5-106.9 63.7L89.9 115.4zm144.9 22.6c-6.2 6.2-6.2 16.4 0 22.6l50.5 50.5c6.2 6.2 16.4 6.2 22.6 0s6.2-16.4 0-22.6l-50.5-50.5c-6.2-6.2-16.4-6.2-22.6 0z"/></svg>',
            ],
            [
                'identifier' => 'pistol',
                'category' => 'weapon',
                'displayName' => 'Pistol',
                'tags' => ['weapon', 'gun', 'firearm', 'ranged'],
                'color' => '#4A5568',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M368 32h144c8.8 0 16 7.2 16 16v96c0 8.8-7.2 16-16 16H432c0 44.2-35.8 80-80 80H288v64h64c17.7 0 32-14.3 32-32V240c0-8.8 7.2-16 16-16h48c8.8 0 16 7.2 16 16v32c0 53-43 96-96 96H288v32c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V352H32c-17.7 0-32-14.3-32-32V224c0-17.7 14.3-32 32-32H64V160c0-17.7 14.3-32 32-32H256c17.7 0 32 14.3 32 32v32h64c26.5 0 48-21.5 48-48V32z"/></svg>',
            ],
            [
                'identifier' => 'rifle',
                'category' => 'weapon',
                'displayName' => 'Rifle',
                'tags' => ['weapon', 'gun', 'firearm', 'ranged'],
                'color' => '#2D3748',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M504 128c4.4 0 8-3.6 8-8v-16c0-4.4-3.6-8-8-8h-56V56c0-4.4-3.6-8-8-8h-32c-4.4 0-8 3.6-8 8v40h-16v-24c0-4.4-3.6-8-8-8h-32c-4.4 0-8 3.6-8 8v24H128V56c0-4.4-3.6-8-8-8H88c-4.4 0-8 3.6-8 8v40H48c-8.8 0-16 7.2-16 16v64H16c-8.8 0-16 7.2-16 16v64c0 8.8 7.2 16 16 16h16v144c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272h368c8.8 0 16-7.2 16-16v-64c0-8.8-7.2-16-16-16h-56v-32h96z"/></svg>',
            ],
            [
                'identifier' => 'shotgun',
                'category' => 'weapon',
                'displayName' => 'Shotgun',
                'tags' => ['weapon', 'gun', 'firearm', 'ranged'],
                'color' => '#744210',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="currentColor" d="M608 64H416V32c0-17.7-14.3-32-32-32h-96c-17.7 0-32 14.3-32 32v32H32C14.3 64 0 78.3 0 96v64c0 17.7 14.3 32 32 32h32v128H32c-17.7 0-32 14.3-32 32v32c0 17.7 14.3 32 32 32h544c17.7 0 32-14.3 32-32v-32c0-17.7-14.3-32-32-32h-32V192h32c17.7 0 32-14.3 32-32V96c0-17.7-14.3-32-32-32zM288 64h64v96h-64V64zM128 320V192h384v128H128z"/></svg>',
            ],

            // ===== MOVEMENT =====
            [
                'identifier' => 'sprint',
                'category' => 'movement',
                'displayName' => 'Sprint',
                'tags' => ['movement', 'run', 'fast', 'speed'],
                'color' => '#48BB78',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path fill="currentColor" d="M208 48c0 26.5-21.5 48-48 48s-48-21.5-48-48s21.5-48 48-48s48 21.5 48 48zM96 128c17.7 0 32 14.3 32 32v48h64V160c0-17.7 14.3-32 32-32s32 14.3 32 32v48h16c17.7 0 32 14.3 32 32s-14.3 32-32 32H240v112c0 44.2-35.8 80-80 80s-80-35.8-80-80V272H64c-17.7 0-32-14.3-32-32s14.3-32 32-32H80V160c0-17.7 14.3-32 32-32h16z"/></svg>',
            ],
            [
                'identifier' => 'walk',
                'category' => 'movement',
                'displayName' => 'Walk',
                'tags' => ['movement', 'slow', 'pace'],
                'color' => '#9AE6B4',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path fill="currentColor" d="M208 48c0 26.5-21.5 48-48 48s-48-21.5-48-48s21.5-48 48-48s48 21.5 48 48zM152 352V480c0 17.7-14.3 32-32 32s-32-14.3-32-32V256.9L59.4 304.5c-9.1 15.1-28.8 20-43.9 10.9s-20-28.8-10.9-43.9l58.3-97c17.4-28.9 48.6-46.6 82.3-46.6h29.7c33.7 0 64.9 17.7 82.3 46.6l58.3 97c9.1 15.1 4.2 34.8-10.9 43.9s-34.8 4.2-43.9-10.9L232 256.9V480c0 17.7-14.3 32-32 32s-32-14.3-32-32V352H152z"/></svg>',
            ],
            [
                'identifier' => 'crouch',
                'category' => 'movement',
                'displayName' => 'Crouch',
                'tags' => ['movement', 'stealth', 'low'],
                'color' => '#805AD5',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M256 48c0 26.5-21.5 48-48 48s-48-21.5-48-48s21.5-48 48-48s48 21.5 48 48zM152 352V480c0 17.7-14.3 32-32 32s-32-14.3-32-32V256.9L59.4 304.5c-9.1 15.1-28.8 20-43.9 10.9s-20-28.8-10.9-43.9l58.3-97c17.4-28.9 48.6-46.6 82.3-46.6h29.7c33.7 0 64.9 17.7 82.3 46.6l58.3 97c9.1 15.1 4.2 34.8-10.9 43.9s-34.8 4.2-43.9-10.9L232 256.9V480c0 17.7-14.3 32-32 32s-32-14.3-32-32V352H152z"/></svg>',
            ],
            [
                'identifier' => 'jump',
                'category' => 'movement',
                'displayName' => 'Jump',
                'tags' => ['movement', 'leap', 'vertical'],
                'color' => '#63B3ED',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M192 0c17.7 0 32 14.3 32 32V64h64c17.7 0 32 14.3 32 32s-14.3 32-32 32H224v32h32c17.7 0 32 14.3 32 32s-14.3 32-32 32H224v64h64c17.7 0 32 14.3 32 32s-14.3 32-32 32H224v96c0 17.7-14.3 32-32 32s-32-14.3-32-32V352H96c-17.7 0-32-14.3-32-32s14.3-32 32-32h64V224H128c-17.7 0-32-14.3-32-32s14.3-32 32-32h32V128H96c-17.7 0-32-14.3-32-32s14.3-32 32-32h64V32c0-17.7 14.3-32 32-32z"/></svg>',
            ],

            // ===== RESOURCES =====
            [
                'identifier' => 'heart',
                'category' => 'resource',
                'displayName' => 'Health',
                'tags' => ['resource', 'health', 'healing', 'life'],
                'color' => '#F56565',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M47.6 300.4L228.3 469.1c7.5 7 17.4 10.9 27.7 10.9s20.2-3.9 27.7-10.9L464.4 300.4c30.4-28.3 47.6-68 47.6-109.5v-5.8c0-69.9-50.5-129.5-119.4-141C347 36.5 300.6 51.4 268 84L256 96 244 84c-32.6-32.6-79-47.5-124.6-39.9C50.5 55.6 0 115.2 0 185.1v5.8c0 41.5 17.2 81.2 47.6 109.5z"/></svg>',
            ],
            [
                'identifier' => 'heart_broken',
                'category' => 'resource',
                'displayName' => 'No Healing',
                'tags' => ['resource', 'health', 'no heal', 'damaged'],
                'color' => '#E53E3E',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M119.4 44.1c23.3-3.9 46.8-1.9 68.6 5.3L246.3 92c-5.8 13.4-9.9 27.8-11.8 42.7l-46.1-20.1-34.6 30.1c-7.6 6.7-18.5 7.8-27.3 2.8S111.2 136.1 112 126L119.4 44.1zM340 376.5l19.8 25.6c7.6 9.7 9.5 22.9 4.9 34.7s-15.4 20.2-28 22.9c-41.7 9-87.7 2.3-125.3-21.4L47.6 300.4C17.2 272.1 0 232.4 0 190.9v-5.8c0-69.9 50.5-129.5 119.4-141l11.4-1.9 19.8-85.1c2.8-12.1 13.3-20.9 25.7-21.4s23.8 7 27.8 18.7L236 74.4l47.1-57.6c5.6-6.9 14-10.8 22.8-10.8c9.7 0 19.1 4.6 25 12.3l16.3 21.3 31.4-16.1c18.1-9.3 40.1-7 56.1 5.9l31 25c12.8 10.3 18.6 27.3 14.6 43.1L464.4 168.4c30.4 28.3 47.6 68 47.6 109.5v5.8c0 38.2-14.3 75.3-40.4 104.4L340 376.5z"/></svg>',
            ],
            [
                'identifier' => 'shield',
                'category' => 'resource',
                'displayName' => 'Shield',
                'tags' => ['resource', 'defense', 'protection', 'armor'],
                'color' => '#4299E1',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M256 0c4.6 0 9.2 1 13.4 2.9L457.7 82.8c22 9.3 38.4 31 38.3 57.2c-.5 99.2-41.3 280.7-213.6 363.2c-16.7 8-36.1 8-52.8 0C57.3 420.7 16.5 239.2 16 140c-.1-26.2 16.3-47.9 38.3-57.2L242.7 2.9C246.8 1 251.4 0 256 0z"/></svg>',
            ],
            [
                'identifier' => 'ammo',
                'category' => 'resource',
                'displayName' => 'Ammo',
                'tags' => ['resource', 'ammunition', 'bullets'],
                'color' => '#D69E2E',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M448 80v320c0 26.5-21.5 48-48 48h-32c-26.5 0-48-21.5-48-48V80c0-26.5 21.5-48 48-48h32c26.5 0 48 21.5 48 48zM288 80v352c0 26.5-21.5 48-48 48h-32c-26.5 0-48-21.5-48-48V80c0-26.5 21.5-48 48-48h32c26.5 0 48 21.5 48 48zM128 80v384c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V80C0 53.5 21.5 32 48 32h32c26.5 0 48 21.5 48 48z"/></svg>',
            ],

            // ===== ACTIONS =====
            [
                'identifier' => 'reload',
                'category' => 'action',
                'displayName' => 'Reload',
                'tags' => ['action', 'reload', 'refresh'],
                'color' => '#319795',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M463.5 224H472c13.3 0 24-10.7 24-24V72c0-9.7-5.8-18.5-14.8-22.2s-19.3-1.7-26.2 5.2L413.4 96.6c-87.6-86.5-228.7-86.2-315.8 1c-87.5 87.5-87.5 229.3 0 316.8s229.3 87.5 316.8 0c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0c-62.5 62.5-163.8 62.5-226.3 0s-62.5-163.8 0-226.3c62.2-62.2 162.7-62.5 225.3-1L327 183c-6.9 6.9-8.9 17.2-5.2 26.2s12.5 14.8 22.2 14.8H463.5z"/></svg>',
            ],
            [
                'identifier' => 'aim',
                'category' => 'action',
                'displayName' => 'Aim',
                'tags' => ['action', 'aim', 'target', 'sight'],
                'color' => '#E53E3E',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M256 512c141.4 0 256-114.6 256-256S397.4 0 256 0S0 114.6 0 256S114.6 512 256 512zm0-96c-88.4 0-160-71.6-160-160s71.6-160 160-160s160 71.6 160 160s-71.6 160-160 160zm0-224c-35.3 0-64 28.7-64 64s28.7 64 64 64s64-28.7 64-64s-28.7-64-64-64z"/></svg>',
            ],
            [
                'identifier' => 'grenade',
                'category' => 'action',
                'displayName' => 'Grenade',
                'tags' => ['action', 'explosive', 'throw'],
                'color' => '#DD6B20',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M349.4 44.6c5.9-13.7 1.5-29.7-10.6-38.5s-28.6-8-39.9 1.8l-256 224c-10 8.8-13.6 22.9-8.9 35.3S50.7 288 64 288H175.5L98.6 467.4c-5.9 13.7-1.5 29.7 10.6 38.5s28.6 8 39.9-1.8l256-224c10-8.8 13.6-22.9 8.9-35.3s-16.6-20.7-30-20.7H272.5L349.4 44.6z"/></svg>',
            ],

            // ===== MODIFIERS =====
            [
                'identifier' => 'prohibited',
                'category' => 'modifier',
                'displayName' => 'Prohibited',
                'tags' => ['modifier', 'no', 'ban', 'forbidden'],
                'color' => '#E53E3E',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M367.2 412.5L99.5 144.8C77.1 176.1 64 214.5 64 256c0 106 86 192 192 192c41.5 0 79.9-13.1 111.2-35.5zm45.3-45.3C434.9 335.9 448 297.5 448 256c0-106-86-192-192-192c-41.5 0-79.9 13.1-111.2 35.5L412.5 367.2zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256z"/></svg>',
            ],
            [
                'identifier' => 'timer',
                'category' => 'modifier',
                'displayName' => 'Timer',
                'tags' => ['modifier', 'time', 'clock', 'duration'],
                'color' => '#805AD5',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M256 0c17.7 0 32 14.3 32 32V64h32c12.9 0 24.6 7.8 29.6 19.8s2.2 25.7-6.9 34.9L334.1 127c12.2 11.3 23.5 23.8 33.6 37.2l8.6-8.6c9.2-9.2 22.9-11.9 34.9-6.9s19.8 16.6 19.8 29.6v32h32c17.7 0 32 14.3 32 32s-14.3 32-32 32H431.5c1 10.5 1.5 21.2 1.5 32c0 123.7-100.3 224-224 224S0 379.7 0 256c0-58.2 22.2-111.2 58.6-151.3L6.1 52.2C-2.8 40.5-.4 23.4 11.3 14.5S40.5-.4 49.4 11.3l52.5 66c6.6 8.3 14.9 15.2 24.1 20.5l8.6-8.6c9.2-9.2 22.9-11.9 34.9-6.9s19.8 16.6 19.8 29.6V144h32V32c0-17.7 14.3-32 32-32zM256 288c17.7 0 32-14.3 32-32V160c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7 14.3 32 32 32z"/></svg>',
            ],
            [
                'identifier' => 'counter',
                'category' => 'modifier',
                'displayName' => 'Counter',
                'tags' => ['modifier', 'count', 'number', 'amount'],
                'color' => '#38B2AC',
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zM96 96H352c17.7 0 32 14.3 32 32s-14.3 32-32 32H96c-17.7 0-32-14.3-32-32s14.3-32 32-32zm0 128H352c17.7 0 32 14.3 32 32s-14.3 32-32 32H96c-17.7 0-32-14.3-32-32s14.3-32 32-32zM64 352c0-17.7 14.3-32 32-32H352c17.7 0 32 14.3 32 32s-14.3 32-32 32H96c-17.7 0-32-14.3-32-32z"/></svg>',
            ],
        ];
    }
}
