<?php

namespace App\DataFixtures;

use App\Entity\Game;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class GameFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $games = $this->getGamesData();
        $images = GameImagesData::getImages();

        foreach ($games as $data) {
            $game = new Game();
            $game->setName($data['name']);
            $game->setDescription($data['description'] ?? null);
            $game->setSteamLink($data['steam_link'] ?? null);
            $game->setEpicLink($data['epic_link'] ?? null);
            $game->setGogLink($data['gog_link'] ?? null);
            $game->setTwitchCategory($data['twitch_category'] ?? null);
            $game->setIsCategoryRepresentative($data['is_representative'] ?? false);
            $game->setIsActive(true);

            // Set image if available
            if (isset($images[$data['name']])) {
                $game->setImage($images[$data['name']]);
            }

            // Add categories
            foreach ($data['categories'] as $categoryRef) {
                /** @var \App\Entity\Category $category */
                $category = $this->getReference($categoryRef, \App\Entity\Category::class);
                $game->addCategory($category);
            }

            $manager->persist($game);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }

    private function getGamesData(): array
    {
        return [
            // Shooters
            [
                'name' => 'Counter-Strike 2',
                'description' => 'The legendary tactical FPS',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER],
                'is_representative' => true,
                'steam_link' => 'https://store.steampowered.com/app/730/CounterStrike_2/',
                'twitch_category' => 'Counter-Strike',
            ],
            [
                'name' => 'VALORANT',
                'description' => 'Character-based tactical shooter',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER],
                'twitch_category' => 'VALORANT',
            ],
            [
                'name' => 'Overwatch 2',
                'description' => 'Team-based hero shooter',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER],
                'twitch_category' => 'Overwatch 2',
            ],

            // Battle Royale
            [
                'name' => 'Call of Duty: Warzone',
                'description' => 'Intense battle royale warfare',
                'categories' => [CategoryFixtures::CATEGORY_BATTLE_ROYALE, CategoryFixtures::CATEGORY_SHOOTER],
                'is_representative' => true,
                'twitch_category' => 'Call of Duty: Warzone',
            ],
            [
                'name' => 'Fortnite',
                'description' => 'Battle royale with building',
                'categories' => [CategoryFixtures::CATEGORY_BATTLE_ROYALE],
                'twitch_category' => 'Fortnite',
            ],
            [
                'name' => 'Apex Legends',
                'description' => 'Character-based battle royale',
                'categories' => [CategoryFixtures::CATEGORY_BATTLE_ROYALE],
                'steam_link' => 'https://store.steampowered.com/app/1172470/Apex_Legends/',
                'twitch_category' => 'Apex Legends',
            ],

            // MOBA
            [
                'name' => 'League of Legends',
                'description' => 'The most popular MOBA',
                'categories' => [CategoryFixtures::CATEGORY_MOBA],
                'is_representative' => true,
                'twitch_category' => 'League of Legends',
            ],
            [
                'name' => 'Dota 2',
                'description' => 'Complex competitive MOBA',
                'categories' => [CategoryFixtures::CATEGORY_MOBA],
                'steam_link' => 'https://store.steampowered.com/app/570/Dota_2/',
                'twitch_category' => 'Dota 2',
            ],

            // Horror
            [
                'name' => 'Resident Evil 2 (2019)',
                'description' => 'Survival horror remake masterpiece',
                'categories' => [CategoryFixtures::CATEGORY_HORROR],
                'is_representative' => true,
                'steam_link' => 'https://store.steampowered.com/app/883710/Resident_Evil_2/',
                'twitch_category' => 'Resident Evil 2',
            ],
            [
                'name' => 'Resident Evil 4 (2023)',
                'description' => 'Action horror remake',
                'categories' => [CategoryFixtures::CATEGORY_HORROR],
                'steam_link' => 'https://store.steampowered.com/app/2050650/Resident_Evil_4/',
                'twitch_category' => 'Resident Evil 4',
            ],
            [
                'name' => 'Resident Evil Village (2021)',
                'description' => 'First-person horror adventure',
                'categories' => [CategoryFixtures::CATEGORY_HORROR],
                'steam_link' => 'https://store.steampowered.com/app/1196590/Resident_Evil_Village/',
                'twitch_category' => 'Resident Evil Village',
            ],
            [
                'name' => 'Resident Evil 7',
                'description' => 'First-person survival horror',
                'categories' => [CategoryFixtures::CATEGORY_HORROR],
                'steam_link' => 'https://store.steampowered.com/app/418370/RESIDENT_EVIL_7_biohazard/',
                'twitch_category' => 'Resident Evil 7: Biohazard',
            ],
            [
                'name' => 'Silent Hill 2',
                'description' => 'Psychological horror classic',
                'categories' => [CategoryFixtures::CATEGORY_HORROR],
                'steam_link' => 'https://store.steampowered.com/app/2124490/Silent_Hill_2/',
                'twitch_category' => 'Silent Hill 2',
            ],
            [
                'name' => 'Phasmophobia',
                'description' => 'Co-op ghost hunting',
                'categories' => [CategoryFixtures::CATEGORY_HORROR],
                'steam_link' => 'https://store.steampowered.com/app/739630/Phasmophobia/',
                'twitch_category' => 'Phasmophobia',
            ],
            [
                'name' => 'Dead by Daylight',
                'description' => 'Asymmetric multiplayer horror',
                'categories' => [CategoryFixtures::CATEGORY_HORROR],
                'steam_link' => 'https://store.steampowered.com/app/381210/Dead_by_Daylight/',
                'twitch_category' => 'Dead by Daylight',
            ],
            [
                'name' => 'Lethal Company',
                'description' => 'Co-op survival horror',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_SURVIVAL],
                'steam_link' => 'https://store.steampowered.com/app/1966720/Lethal_Company/',
                'twitch_category' => 'Lethal Company',
            ],

            // RPG
            [
                'name' => 'Elden Ring',
                'description' => 'Open-world action RPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'is_representative' => true,
                'steam_link' => 'https://store.steampowered.com/app/1245620/ELDEN_RING/',
                'twitch_category' => 'Elden Ring',
            ],
            [
                'name' => 'Baldur\'s Gate 3',
                'description' => 'Epic D&D RPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/1086940/Baldurs_Gate_3/',
                'twitch_category' => 'Baldur\'s Gate 3',
            ],
            [
                'name' => 'Cyberpunk 2077',
                'description' => 'Futuristic open-world RPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/1091500/Cyberpunk_2077/',
                'twitch_category' => 'Cyberpunk 2077',
            ],
            [
                'name' => 'Final Fantasy XIV',
                'description' => 'MMORPG with epic story',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/39210/FINAL_FANTASY_XIV_Online/',
                'twitch_category' => 'Final Fantasy XIV Online',
            ],
            [
                'name' => 'World of Warcraft',
                'description' => 'The legendary MMORPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'twitch_category' => 'World of Warcraft',
            ],
            [
                'name' => 'Secret of Mana',
                'description' => 'Classic action RPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/637670/Secret_of_Mana/',
                'twitch_category' => 'Secret of Mana',
            ],
            [
                'name' => 'Chrono Trigger',
                'description' => 'Timeless JRPG classic',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/613830/CHRONO_TRIGGER/',
                'twitch_category' => 'Chrono Trigger',
            ],

            // Roguelike
            [
                'name' => 'The Binding of Isaac',
                'description' => 'Dungeon crawler roguelike',
                'categories' => [CategoryFixtures::CATEGORY_ROGUELIKE],
                'is_representative' => true,
                'steam_link' => 'https://store.steampowered.com/app/250900/The_Binding_of_Isaac_Rebirth/',
                'twitch_category' => 'The Binding of Isaac: Rebirth',
            ],
            [
                'name' => 'Hades',
                'description' => 'Action roguelike masterpiece',
                'categories' => [CategoryFixtures::CATEGORY_ROGUELIKE],
                'steam_link' => 'https://store.steampowered.com/app/1145360/Hades/',
                'twitch_category' => 'Hades',
            ],

            // Platformer
            [
                'name' => 'Super Mario 64',
                'description' => '3D platformer classic',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER],
                'is_representative' => true,
                'twitch_category' => 'Super Mario 64',
            ],
            [
                'name' => 'Super Mario World',
                'description' => '2D platformer perfection',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER],
                'twitch_category' => 'Super Mario World',
            ],
            [
                'name' => 'Super Mario Galaxy',
                'description' => 'Gravity-defying platformer',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER],
                'twitch_category' => 'Super Mario Galaxy',
            ],
            [
                'name' => 'Celeste',
                'description' => 'Challenging precision platformer',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER],
                'steam_link' => 'https://store.steampowered.com/app/504230/Celeste/',
                'twitch_category' => 'Celeste',
            ],

            // Fighting
            [
                'name' => 'Street Fighter 6',
                'description' => 'Modern fighting game evolution',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING],
                'is_representative' => true,
                'steam_link' => 'https://store.steampowered.com/app/1364780/Street_Fighter_6/',
                'twitch_category' => 'Street Fighter 6',
            ],
            [
                'name' => 'Tekken 8',
                'description' => '3D fighting game',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING],
                'steam_link' => 'https://store.steampowered.com/app/1778820/TEKKEN_8/',
                'twitch_category' => 'Tekken 8',
            ],
            [
                'name' => 'Mortal Kombat 1',
                'description' => 'Brutal fighting action',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING],
                'steam_link' => 'https://store.steampowered.com/app/1971870/Mortal_Kombat_1/',
                'twitch_category' => 'Mortal Kombat 1',
            ],

            // Survival
            [
                'name' => 'Rust',
                'description' => 'Hardcore multiplayer survival',
                'categories' => [CategoryFixtures::CATEGORY_SURVIVAL],
                'is_representative' => true,
                'steam_link' => 'https://store.steampowered.com/app/252490/Rust/',
                'twitch_category' => 'Rust',
            ],
            [
                'name' => 'Minecraft',
                'description' => 'Sandbox survival crafting',
                'categories' => [CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_RPG],
                'twitch_category' => 'Minecraft',
            ],
            [
                'name' => 'Terraria',
                'description' => '2D sandbox adventure',
                'categories' => [CategoryFixtures::CATEGORY_SURVIVAL],
                'steam_link' => 'https://store.steampowered.com/app/105600/Terraria/',
                'twitch_category' => 'Terraria',
            ],

            // Retro Shooters
            [
                'name' => 'DOOM Eternal',
                'description' => 'Fast-paced demon slaying',
                'categories' => [CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_SHOOTER],
                'is_representative' => true,
                'steam_link' => 'https://store.steampowered.com/app/782330/DOOM_Eternal/',
                'twitch_category' => 'DOOM Eternal',
            ],
            [
                'name' => 'DOOM (2016)',
                'description' => 'Modern DOOM reimagining',
                'categories' => [CategoryFixtures::CATEGORY_RETRO_SHOOTER],
                'steam_link' => 'https://store.steampowered.com/app/379720/DOOM/',
                'twitch_category' => 'DOOM (2016)',
            ],
            [
                'name' => 'ULTRAKILL',
                'description' => 'High-octane retro FPS',
                'categories' => [CategoryFixtures::CATEGORY_RETRO_SHOOTER],
                'steam_link' => 'https://store.steampowered.com/app/1229490/ULTRAKILL/',
                'twitch_category' => 'ULTRAKILL',
            ],
            [
                'name' => 'Quake',
                'description' => 'Classic arena shooter',
                'categories' => [CategoryFixtures::CATEGORY_RETRO_SHOOTER],
                'steam_link' => 'https://store.steampowered.com/app/2310/Quake/',
                'twitch_category' => 'Quake',
            ],
            [
                'name' => 'Dusk',
                'description' => 'Fast retro shooter',
                'categories' => [CategoryFixtures::CATEGORY_RETRO_SHOOTER],
                'steam_link' => 'https://store.steampowered.com/app/519860/DUSK/',
                'twitch_category' => 'Dusk',
            ],

            // Additional Popular Games
            [
                'name' => 'Grand Theft Auto V',
                'description' => 'Open-world crime saga',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER],
                'steam_link' => 'https://store.steampowered.com/app/271590/Grand_Theft_Auto_V/',
                'twitch_category' => 'Grand Theft Auto V',
            ],
            [
                'name' => 'Red Dead Redemption 2',
                'description' => 'Wild west adventure',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/1174180/Red_Dead_Redemption_2/',
                'twitch_category' => 'Red Dead Redemption 2',
            ],
            [
                'name' => 'Starfield',
                'description' => 'Space exploration RPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/1716740/Starfield/',
                'twitch_category' => 'Starfield',
            ],
            [
                'name' => 'Hogwarts Legacy',
                'description' => 'Wizarding world RPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/990080/Hogwarts_Legacy/',
                'twitch_category' => 'Hogwarts Legacy',
            ],
        ];
    }
}
