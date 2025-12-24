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
            // ========================================
            // CALL OF DUTY FRANCHISE
            // ========================================
            [
                'name' => 'Call of Duty (2003)',
                'description' => 'The original World War II shooter',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/2620/Call_of_Duty/',
                'twitch_category' => 'Call of Duty',
            ],
            [
                'name' => 'Call of Duty 2 (2005)',
                'description' => 'WWII shooter sequel',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/2630/Call_of_Duty_2/',
                'twitch_category' => 'Call of Duty 2',
            ],
            [
                'name' => 'Call of Duty 4: Modern Warfare (2007)',
                'description' => 'Revolutionary modern military shooter',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/7940/Call_of_Duty_4_Modern_Warfare/',
                'twitch_category' => 'Call of Duty 4: Modern Warfare',
            ],
            [
                'name' => 'Call of Duty: Modern Warfare 2 (2009)',
                'description' => 'Iconic military shooter',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/10180/Call_of_Duty_Modern_Warfare_2/',
                'twitch_category' => 'Call of Duty: Modern Warfare 2',
            ],
            [
                'name' => 'Call of Duty: Black Ops (2010)',
                'description' => 'Cold War era shooter',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/42700/Call_of_Duty_Black_Ops/',
                'twitch_category' => 'Call of Duty: Black Ops',
            ],
            [
                'name' => 'Call of Duty: Modern Warfare 3 (2011)',
                'description' => 'Continuation of Modern Warfare saga',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/115300/Call_of_Duty_Modern_Warfare_3/',
                'twitch_category' => 'Call of Duty: Modern Warfare 3',
            ],
            [
                'name' => 'Call of Duty: Black Ops 2 (2012)',
                'description' => 'Future warfare shooter',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/202970/Call_of_Duty_Black_Ops_II/',
                'twitch_category' => 'Call of Duty: Black Ops 2',
            ],
            [
                'name' => 'Call of Duty: Black Ops 3 (2015)',
                'description' => 'Futuristic military shooter',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/311210/Call_of_Duty_Black_Ops_III/',
                'twitch_category' => 'Call of Duty: Black Ops 3',
            ],
            [
                'name' => 'Call of Duty: Black Ops 4 (2018)',
                'description' => 'Multiplayer-focused shooter',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_BATTLE_ROYALE],
                'steam_link' => 'https://store.steampowered.com/app/756340/Call_of_Duty_Black_Ops_4/',
                'twitch_category' => 'Call of Duty: Black Ops 4',
            ],
            [
                'name' => 'Call of Duty: Modern Warfare (2019)',
                'description' => 'Modern Warfare reboot',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1938090/Call_of_Duty_Modern_Warfare/',
                'twitch_category' => 'Call of Duty: Modern Warfare',
            ],
            [
                'name' => 'Call of Duty: Black Ops Cold War (2020)',
                'description' => 'Cold War era Black Ops',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1690890/Call_of_Duty_Black_Ops_Cold_War/',
                'twitch_category' => 'Call of Duty: Black Ops Cold War',
            ],
            [
                'name' => 'Call of Duty: Vanguard (2021)',
                'description' => 'WWII shooter',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1898850/Call_of_Duty_Vanguard/',
                'twitch_category' => 'Call of Duty: Vanguard',
            ],
            [
                'name' => 'Call of Duty: Modern Warfare II (2022)',
                'description' => 'Modern Warfare sequel',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1938090/Call_of_Duty_Modern_Warfare_II/',
                'twitch_category' => 'Call of Duty: Modern Warfare II',
            ],
            [
                'name' => 'Call of Duty: Modern Warfare III (2023)',
                'description' => 'Latest Modern Warfare entry',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/2519060/Call_of_Duty_Modern_Warfare_III/',
                'twitch_category' => 'Call of Duty: Modern Warfare III',
            ],

            // ========================================
            // RESIDENT EVIL FRANCHISE
            // ========================================
            [
                'name' => 'Resident Evil (1996)',
                'description' => 'The survival horror classic',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ADVENTURE],
                'twitch_category' => 'Resident Evil',
            ],
            [
                'name' => 'Resident Evil 2 (1998)',
                'description' => 'Survival horror masterpiece',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ADVENTURE],
                'twitch_category' => 'Resident Evil 2',
            ],
            [
                'name' => 'Resident Evil 3: Nemesis (1999)',
                'description' => 'Pursued by an unstoppable foe',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ADVENTURE],
                'twitch_category' => 'Resident Evil 3: Nemesis',
            ],
            [
                'name' => 'Resident Evil Code: Veronica (2000)',
                'description' => 'Dreamcast survival horror',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ADVENTURE],
                'twitch_category' => 'Resident Evil Code: Veronica',
            ],
            [
                'name' => 'Resident Evil 4 (2005)',
                'description' => 'Action horror revolution',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ACTION_ADVENTURE, CategoryFixtures::CATEGORY_SHOOTER],
                'steam_link' => 'https://store.steampowered.com/app/254700/Resident_Evil_4/',
                'twitch_category' => 'Resident Evil 4',
            ],
            [
                'name' => 'Resident Evil 5 (2009)',
                'description' => 'Co-op action horror',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ACTION_ADVENTURE, CategoryFixtures::CATEGORY_SHOOTER],
                'steam_link' => 'https://store.steampowered.com/app/21690/Resident_Evil_5/',
                'twitch_category' => 'Resident Evil 5',
            ],
            [
                'name' => 'Resident Evil 6 (2012)',
                'description' => 'Multiple campaign action horror',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ACTION_ADVENTURE, CategoryFixtures::CATEGORY_SHOOTER],
                'steam_link' => 'https://store.steampowered.com/app/221040/Resident_Evil_6/',
                'twitch_category' => 'Resident Evil 6',
            ],
            [
                'name' => 'Resident Evil 7: Biohazard (2017)',
                'description' => 'First-person survival horror return',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/418370/RESIDENT_EVIL_7_biohazard/',
                'twitch_category' => 'Resident Evil 7: Biohazard',
            ],
            [
                'name' => 'Resident Evil Village (2021)',
                'description' => 'Gothic horror adventure',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ACTION_ADVENTURE, CategoryFixtures::CATEGORY_SURVIVAL],
                'steam_link' => 'https://store.steampowered.com/app/1196590/Resident_Evil_Village/',
                'twitch_category' => 'Resident Evil Village',
            ],
            [
                'name' => 'Resident Evil 2 (2019)',
                'description' => 'Survival horror remake masterpiece',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/883710/Resident_Evil_2/',
                'twitch_category' => 'Resident Evil 2',
            ],
            [
                'name' => 'Resident Evil 3 (2020)',
                'description' => 'Nemesis remake',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ACTION_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/952060/Resident_Evil_3/',
                'twitch_category' => 'Resident Evil 3',
            ],
            [
                'name' => 'Resident Evil 4 (2023)',
                'description' => 'RE4 remake',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ACTION_ADVENTURE, CategoryFixtures::CATEGORY_SHOOTER],
                'steam_link' => 'https://store.steampowered.com/app/2050650/Resident_Evil_4/',
                'twitch_category' => 'Resident Evil 4',
            ],

            // ========================================
            // BATTLEFIELD FRANCHISE
            // ========================================
            [
                'name' => 'Battlefield 1942 (2002)',
                'description' => 'The original large-scale warfare',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER],
                'twitch_category' => 'Battlefield 1942',
            ],
            [
                'name' => 'Battlefield 2 (2005)',
                'description' => 'Modern military combat',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER],
                'twitch_category' => 'Battlefield 2',
            ],
            [
                'name' => 'Battlefield: Bad Company 2 (2010)',
                'description' => 'Destruction-focused warfare',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER],
                'steam_link' => 'https://store.steampowered.com/app/24960/Battlefield_Bad_Company_2/',
                'twitch_category' => 'Battlefield: Bad Company 2',
            ],
            [
                'name' => 'Battlefield 3 (2011)',
                'description' => 'Modern military shooter',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER],
                'twitch_category' => 'Battlefield 3',
            ],
            [
                'name' => 'Battlefield 4 (2013)',
                'description' => 'Large-scale multiplayer warfare',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER],
                'twitch_category' => 'Battlefield 4',
            ],
            [
                'name' => 'Battlefield 1 (2016)',
                'description' => 'WWI battlefield experience',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER],
                'twitch_category' => 'Battlefield 1',
            ],
            [
                'name' => 'Battlefield V (2018)',
                'description' => 'WWII warfare',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER],
                'twitch_category' => 'Battlefield V',
            ],
            [
                'name' => 'Battlefield 2042 (2021)',
                'description' => 'Near-future warfare',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER],
                'steam_link' => 'https://store.steampowered.com/app/1517290/Battlefield_2042/',
                'twitch_category' => 'Battlefield 2042',
            ],

            // ========================================
            // COUNTER-STRIKE SERIES
            // ========================================
            [
                'name' => 'Counter-Strike 1.6 (1999)',
                'description' => 'The legendary tactical shooter',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER],
                'steam_link' => 'https://store.steampowered.com/app/10/CounterStrike/',
                'twitch_category' => 'Counter-Strike',
            ],
            [
                'name' => 'Counter-Strike: Source (2004)',
                'description' => 'Source engine remake',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER],
                'steam_link' => 'https://store.steampowered.com/app/240/CounterStrike_Source/',
                'twitch_category' => 'Counter-Strike: Source',
            ],
            [
                'name' => 'Counter-Strike: Global Offensive (2012)',
                'description' => 'Competitive tactical FPS',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER],
                'steam_link' => 'https://store.steampowered.com/app/730/CounterStrike_Global_Offensive/',
                'twitch_category' => 'Counter-Strike',
            ],
            [
                'name' => 'Counter-Strike 2 (2023)',
                'description' => 'Next-gen CS',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER],
                'steam_link' => 'https://store.steampowered.com/app/730/CounterStrike_2/',
                'twitch_category' => 'Counter-Strike',
            ],

            // ========================================
            // OTHER SHOOTERS
            // ========================================
            [
                'name' => 'VALORANT',
                'description' => 'Character-based tactical shooter',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER],
                'twitch_category' => 'VALORANT',
            ],
            [
                'name' => 'Overwatch 2',
                'description' => 'Team-based hero shooter',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'twitch_category' => 'Overwatch 2',
            ],

            // Battle Royale
            [
                'name' => 'Call of Duty: Warzone',
                'description' => 'Intense battle royale warfare',
                'categories' => [CategoryFixtures::CATEGORY_BATTLE_ROYALE, CategoryFixtures::CATEGORY_SHOOTER],
                'twitch_category' => 'Call of Duty: Warzone',
            ],
            [
                'name' => 'Fortnite',
                'description' => 'Battle royale with building',
                'categories' => [CategoryFixtures::CATEGORY_BATTLE_ROYALE, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SHOOTER],
                'twitch_category' => 'Fortnite',
            ],
            [
                'name' => 'Apex Legends',
                'description' => 'Character-based battle royale',
                'categories' => [CategoryFixtures::CATEGORY_BATTLE_ROYALE, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SHOOTER],
                'steam_link' => 'https://store.steampowered.com/app/1172470/Apex_Legends/',
                'twitch_category' => 'Apex Legends',
            ],

            // MOBA
            [
                'name' => 'League of Legends',
                'description' => 'The most popular MOBA',
                'categories' => [CategoryFixtures::CATEGORY_MOBA],
                'twitch_category' => 'League of Legends',
            ],
            [
                'name' => 'Dota 2',
                'description' => 'Complex competitive MOBA',
                'categories' => [CategoryFixtures::CATEGORY_MOBA],
                'steam_link' => 'https://store.steampowered.com/app/570/Dota_2/',
                'twitch_category' => 'Dota 2',
            ],

            // Horror (Additional Titles)
            [
                'name' => 'Resident Evil 7',
                'description' => 'First-person survival horror',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ADVENTURE],
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
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SURVIVAL],
                'steam_link' => 'https://store.steampowered.com/app/739630/Phasmophobia/',
                'twitch_category' => 'Phasmophobia',
            ],
            [
                'name' => 'Dead by Daylight',
                'description' => 'Asymmetric multiplayer horror',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SURVIVAL],
                'steam_link' => 'https://store.steampowered.com/app/381210/Dead_by_Daylight/',
                'twitch_category' => 'Dead by Daylight',
            ],
            [
                'name' => 'Lethal Company',
                'description' => 'Co-op survival horror',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1966720/Lethal_Company/',
                'twitch_category' => 'Lethal Company',
            ],

            // RPG
            [
                'name' => 'Elden Ring',
                'description' => 'Open-world action RPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SOULSLIKE, CategoryFixtures::CATEGORY_ADVENTURE, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/1245620/ELDEN_RING/',
                'twitch_category' => 'Elden Ring',
            ],
            [
                'name' => 'Baldur\'s Gate 3',
                'description' => 'Epic D&D RPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ADVENTURE, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1086940/Baldurs_Gate_3/',
                'twitch_category' => 'Baldur\'s Gate 3',
            ],
            [
                'name' => 'Cyberpunk 2077',
                'description' => 'Futuristic open-world RPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_OPEN_WORLD, CategoryFixtures::CATEGORY_ADVENTURE],
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
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/637670/Secret_of_Mana/',
                'twitch_category' => 'Secret of Mana',
            ],
            [
                'name' => 'Chrono Trigger',
                'description' => 'Timeless JRPG classic',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/613830/CHRONO_TRIGGER/',
                'twitch_category' => 'Chrono Trigger',
            ],

            // Roguelike
            [
                'name' => 'The Binding of Isaac',
                'description' => 'Dungeon crawler roguelike',
                'categories' => [CategoryFixtures::CATEGORY_ROGUELIKE, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/250900/The_Binding_of_Isaac_Rebirth/',
                'twitch_category' => 'The Binding of Isaac: Rebirth',
            ],
            [
                'name' => 'Hades',
                'description' => 'Action roguelike masterpiece',
                'categories' => [CategoryFixtures::CATEGORY_ROGUELIKE, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1145360/Hades/',
                'twitch_category' => 'Hades',
            ],

            // Platformer
            [
                'name' => 'Super Mario 64',
                'description' => '3D platformer classic',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'twitch_category' => 'Super Mario 64',
            ],
            [
                'name' => 'Super Mario World',
                'description' => '2D platformer perfection',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'twitch_category' => 'Super Mario World',
            ],
            [
                'name' => 'Super Mario Galaxy',
                'description' => 'Gravity-defying platformer',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'twitch_category' => 'Super Mario Galaxy',
            ],
            [
                'name' => 'Celeste',
                'description' => 'Challenging precision platformer',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/504230/Celeste/',
                'twitch_category' => 'Celeste',
            ],

            // Fighting
            [
                'name' => 'Street Fighter 6',
                'description' => 'Modern fighting game evolution',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING],
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
                'categories' => [CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_OPEN_WORLD, CategoryFixtures::CATEGORY_SHOOTER],
                'steam_link' => 'https://store.steampowered.com/app/252490/Rust/',
                'twitch_category' => 'Rust',
            ],
            [
                'name' => 'Minecraft',
                'description' => 'Sandbox survival crafting',
                'categories' => [CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ADVENTURE, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'twitch_category' => 'Minecraft',
            ],
            [
                'name' => 'Terraria',
                'description' => '2D sandbox adventure',
                'categories' => [CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/105600/Terraria/',
                'twitch_category' => 'Terraria',
            ],

            // Retro Shooters
            [
                'name' => 'DOOM Eternal',
                'description' => 'Fast-paced demon slaying',
                'categories' => [CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_SHOOTER],
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
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE, CategoryFixtures::CATEGORY_OPEN_WORLD, CategoryFixtures::CATEGORY_ACTION_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/271590/Grand_Theft_Auto_V/',
                'twitch_category' => 'Grand Theft Auto V',
            ],
            [
                'name' => 'Red Dead Redemption 2',
                'description' => 'Wild west adventure',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE, CategoryFixtures::CATEGORY_OPEN_WORLD, CategoryFixtures::CATEGORY_ACTION_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/1174180/Red_Dead_Redemption_2/',
                'twitch_category' => 'Red Dead Redemption 2',
            ],
            [
                'name' => 'Starfield',
                'description' => 'Space exploration RPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ADVENTURE, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/1716740/Starfield/',
                'twitch_category' => 'Starfield',
            ],
            [
                'name' => 'Hogwarts Legacy',
                'description' => 'Wizarding world RPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE, CategoryFixtures::CATEGORY_OPEN_WORLD, CategoryFixtures::CATEGORY_ACTION_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/990080/Hogwarts_Legacy/',
                'twitch_category' => 'Hogwarts Legacy',
            ],

            // ========================================
            // SOULSBORNE SERIES (Speedrun/Challenge Favorites)
            // ========================================
            [
                'name' => 'Dark Souls (2011)',
                'description' => 'The original punishing action RPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SOULSLIKE, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/211420/Dark_Souls_Prepare_to_Die_Edition/',
                'twitch_category' => 'Dark Souls',
            ],
            [
                'name' => 'Dark Souls II (2014)',
                'description' => 'Challenging action RPG sequel',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SOULSLIKE, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/335300/Dark_Souls_II_Scholar_of_the_First_Sin/',
                'twitch_category' => 'Dark Souls II',
            ],
            [
                'name' => 'Dark Souls III (2016)',
                'description' => 'Dark fantasy action RPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SOULSLIKE, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/374320/Dark_Souls_III/',
                'twitch_category' => 'Dark Souls III',
            ],
            [
                'name' => 'Demon\'s Souls (2009)',
                'description' => 'The predecessor to Dark Souls',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SOULSLIKE, CategoryFixtures::CATEGORY_ADVENTURE],
                'twitch_category' => 'Demon\'s Souls',
            ],
            [
                'name' => 'Bloodborne (2015)',
                'description' => 'Gothic horror action RPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SOULSLIKE, CategoryFixtures::CATEGORY_ADVENTURE],
                'twitch_category' => 'Bloodborne',
            ],
            [
                'name' => 'Sekiro: Shadows Die Twice (2019)',
                'description' => 'Samurai action adventure',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_SOULSLIKE, CategoryFixtures::CATEGORY_ACTION_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/814380/Sekiro_Shadows_Die_Twice/',
                'twitch_category' => 'Sekiro: Shadows Die Twice',
            ],

            // ========================================
            // ZELDA SERIES (Speedrun/Randomizer Classics)
            // ========================================
            [
                'name' => 'The Legend of Zelda: A Link to the Past (1991)',
                'description' => 'Classic SNES adventure',
                'categories' => [CategoryFixtures::CATEGORY_ACTION_ADVENTURE, CategoryFixtures::CATEGORY_ADVENTURE, CategoryFixtures::CATEGORY_ACTION],
                'twitch_category' => 'The Legend of Zelda: A Link to the Past',
            ],
            [
                'name' => 'The Legend of Zelda: Ocarina of Time (1998)',
                'description' => 'Revolutionary 3D adventure',
                'categories' => [CategoryFixtures::CATEGORY_ACTION_ADVENTURE, CategoryFixtures::CATEGORY_ADVENTURE, CategoryFixtures::CATEGORY_ACTION],
                'twitch_category' => 'The Legend of Zelda: Ocarina of Time',
            ],
            [
                'name' => 'The Legend of Zelda: Majora\'s Mask (2000)',
                'description' => '3-day time loop adventure',
                'categories' => [CategoryFixtures::CATEGORY_ACTION_ADVENTURE, CategoryFixtures::CATEGORY_ADVENTURE, CategoryFixtures::CATEGORY_ACTION],
                'twitch_category' => 'The Legend of Zelda: Majora\'s Mask',
            ],
            [
                'name' => 'The Legend of Zelda: The Wind Waker (2002)',
                'description' => 'Cel-shaded seafaring adventure',
                'categories' => [CategoryFixtures::CATEGORY_ACTION_ADVENTURE, CategoryFixtures::CATEGORY_ADVENTURE, CategoryFixtures::CATEGORY_ACTION],
                'twitch_category' => 'The Legend of Zelda: The Wind Waker',
            ],
            [
                'name' => 'The Legend of Zelda: Twilight Princess (2006)',
                'description' => 'Wolf transformation adventure',
                'categories' => [CategoryFixtures::CATEGORY_ACTION_ADVENTURE, CategoryFixtures::CATEGORY_ADVENTURE, CategoryFixtures::CATEGORY_ACTION],
                'twitch_category' => 'The Legend of Zelda: Twilight Princess',
            ],
            [
                'name' => 'The Legend of Zelda: Skyward Sword (2011)',
                'description' => 'Origin story adventure',
                'categories' => [CategoryFixtures::CATEGORY_ACTION_ADVENTURE, CategoryFixtures::CATEGORY_ADVENTURE, CategoryFixtures::CATEGORY_ACTION],
                'twitch_category' => 'The Legend of Zelda: Skyward Sword',
            ],
            [
                'name' => 'The Legend of Zelda: Breath of the Wild (2017)',
                'description' => 'Open-world exploration masterpiece',
                'categories' => [CategoryFixtures::CATEGORY_ACTION_ADVENTURE, CategoryFixtures::CATEGORY_ADVENTURE, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'twitch_category' => 'The Legend of Zelda: Breath of the Wild',
            ],
            [
                'name' => 'The Legend of Zelda: Tears of the Kingdom (2023)',
                'description' => 'Build and explore Hyrule',
                'categories' => [CategoryFixtures::CATEGORY_ACTION_ADVENTURE, CategoryFixtures::CATEGORY_ADVENTURE, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'twitch_category' => 'The Legend of Zelda: Tears of the Kingdom',
            ],
            [
                'name' => 'The Legend of Zelda: Link\'s Awakening (1993)',
                'description' => 'Game Boy classic',
                'categories' => [CategoryFixtures::CATEGORY_ACTION_ADVENTURE, CategoryFixtures::CATEGORY_ADVENTURE, CategoryFixtures::CATEGORY_ACTION],
                'twitch_category' => 'The Legend of Zelda: Link\'s Awakening',
            ],

            // ========================================
            // MARIO SERIES (Speedrun/Challenge Favorites)
            // ========================================
            [
                'name' => 'Super Mario Bros. (1985)',
                'description' => 'The original platforming legend',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'twitch_category' => 'Super Mario Bros.',
            ],
            [
                'name' => 'Super Mario Bros. 3 (1988)',
                'description' => 'NES platforming perfection',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'twitch_category' => 'Super Mario Bros. 3',
            ],
            [
                'name' => 'Super Mario Sunshine (2002)',
                'description' => 'Tropical island adventure',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'twitch_category' => 'Super Mario Sunshine',
            ],
            [
                'name' => 'Super Mario Galaxy (2007)',
                'description' => 'Gravity-defying platformer',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'twitch_category' => 'Super Mario Galaxy',
            ],
            [
                'name' => 'Super Mario Galaxy 2 (2010)',
                'description' => 'More galactic platforming',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'twitch_category' => 'Super Mario Galaxy 2',
            ],
            [
                'name' => 'Super Mario Odyssey (2017)',
                'description' => 'Cap-throwing adventure',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'twitch_category' => 'Super Mario Odyssey',
            ],
            [
                'name' => 'Super Mario Maker 2 (2019)',
                'description' => 'Create and play Mario levels',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'twitch_category' => 'Super Mario Maker 2',
            ],

            // ========================================
            // METROID SERIES (Speedrun Classics)
            // ========================================
            [
                'name' => 'Super Metroid (1994)',
                'description' => 'SNES exploration masterpiece',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_METROIDVANIA, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'twitch_category' => 'Super Metroid',
            ],
            [
                'name' => 'Metroid Prime (2002)',
                'description' => 'First-person exploration',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE, CategoryFixtures::CATEGORY_METROIDVANIA],
                'twitch_category' => 'Metroid Prime',
            ],
            [
                'name' => 'Metroid Dread (2021)',
                'description' => '2D Metroid returns',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_METROIDVANIA, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'twitch_category' => 'Metroid Dread',
            ],

            // ========================================
            // INDIE HITS (Speedrun/Challenge Community)
            // ========================================
            [
                'name' => 'Hollow Knight (2017)',
                'description' => 'Challenging metroidvania',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_METROIDVANIA, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/367520/Hollow_Knight/',
                'twitch_category' => 'Hollow Knight',
            ],
            [
                'name' => 'Cuphead (2017)',
                'description' => 'Run and gun boss rush',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/268910/Cuphead/',
                'twitch_category' => 'Cuphead',
            ],
            [
                'name' => 'Ori and the Blind Forest (2015)',
                'description' => 'Beautiful metroidvania',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_METROIDVANIA, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/261570/Ori_and_the_Blind_Forest/',
                'twitch_category' => 'Ori and the Blind Forest',
            ],
            [
                'name' => 'Ori and the Will of the Wisps (2020)',
                'description' => 'Expanded exploration',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_METROIDVANIA, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/1057090/Ori_and_the_Will_of_the_Wisps/',
                'twitch_category' => 'Ori and the Will of the Wisps',
            ],
            [
                'name' => 'Dead Cells (2018)',
                'description' => 'Roguelike action platformer',
                'categories' => [CategoryFixtures::CATEGORY_ROGUELIKE, CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_METROIDVANIA],
                'steam_link' => 'https://store.steampowered.com/app/588650/Dead_Cells/',
                'twitch_category' => 'Dead Cells',
            ],
            [
                'name' => 'Slay the Spire (2019)',
                'description' => 'Deck-building roguelike',
                'categories' => [CategoryFixtures::CATEGORY_ROGUELIKE],
                'steam_link' => 'https://store.steampowered.com/app/646570/Slay_the_Spire/',
                'twitch_category' => 'Slay the Spire',
            ],
            [
                'name' => 'Risk of Rain 2 (2020)',
                'description' => '3D roguelike shooter',
                'categories' => [CategoryFixtures::CATEGORY_ROGUELIKE, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/632360/Risk_of_Rain_2/',
                'twitch_category' => 'Risk of Rain 2',
            ],
            [
                'name' => 'Spelunky 2 (2020)',
                'description' => 'Procedural cave exploration',
                'categories' => [CategoryFixtures::CATEGORY_ROGUELIKE, CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/418530/Spelunky_2/',
                'twitch_category' => 'Spelunky 2',
            ],
            [
                'name' => 'Stardew Valley (2016)',
                'description' => 'Farming RPG simulation',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/413150/Stardew_Valley/',
                'twitch_category' => 'Stardew Valley',
            ],
            [
                'name' => 'Undertale (2015)',
                'description' => 'RPG where nobody has to die',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/391540/Undertale/',
                'twitch_category' => 'Undertale',
            ],
        ];
    }
}
