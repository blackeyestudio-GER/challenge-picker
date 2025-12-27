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

            // ========================================
            // DOOM FRANCHISE (Category Rep + Series)
            // ========================================
            [
                'name' => 'DOOM (1993)',
                'description' => 'Legendary FPS that defined a genre',
                'categories' => [CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/2280/DOOM_1993/',
                'gog_link' => 'https://www.gog.com/game/doom_1993',
                'twitch_category' => 'DOOM',
                'is_representative' => true, // Retro Shooter category rep
            ],
            [
                'name' => 'DOOM II: Hell on Earth (1994)',
                'description' => 'More demons, more guns, more chaos',
                'categories' => [CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/2300/DOOM_II/',
                'gog_link' => 'https://www.gog.com/game/doom_ii_final_doom',
                'twitch_category' => 'DOOM II: Hell on Earth',
            ],
            [
                'name' => 'DOOM (2016)',
                'description' => 'Fast-paced modern reboot',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/379720/DOOM/',
                'twitch_category' => 'DOOM (2016)',
            ],
            [
                'name' => 'DOOM Eternal (2020)',
                'description' => 'Glory kills and resource management',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/782330/DOOM_Eternal/',
                'twitch_category' => 'DOOM Eternal',
            ],

            // ========================================
            // HALF-LIFE FRANCHISE
            // ========================================
            [
                'name' => 'Half-Life (1998)',
                'description' => 'Revolutionary story-driven FPS',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/70/HalfLife/',
                'twitch_category' => 'Half-Life',
            ],
            [
                'name' => 'Half-Life 2 (2004)',
                'description' => 'Physics-based FPS masterpiece',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/220/HalfLife_2/',
                'twitch_category' => 'Half-Life 2',
            ],
            [
                'name' => 'Portal (2007)',
                'description' => 'Mind-bending puzzle shooter',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/400/Portal/',
                'twitch_category' => 'Portal',
            ],
            [
                'name' => 'Portal 2 (2011)',
                'description' => 'Expanded co-op puzzle adventure',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/620/Portal_2/',
                'twitch_category' => 'Portal 2',
            ],

            // ========================================
            // HALO FRANCHISE
            // ========================================
            [
                'name' => 'Halo: Combat Evolved (2001)',
                'description' => 'Xbox launch title that defined console FPS',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1064220/Halo_The_Master_Chief_Collection/',
                'twitch_category' => 'Halo: The Master Chief Collection',
            ],
            [
                'name' => 'Halo 2 (2004)',
                'description' => 'Online multiplayer revolution',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1064220/Halo_The_Master_Chief_Collection/',
                'twitch_category' => 'Halo: The Master Chief Collection',
            ],
            [
                'name' => 'Halo 3 (2007)',
                'description' => 'Finish the fight',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1064220/Halo_The_Master_Chief_Collection/',
                'twitch_category' => 'Halo: The Master Chief Collection',
            ],
            [
                'name' => 'Halo Infinite (2021)',
                'description' => 'Open-world Halo adventure',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1240440/Halo_Infinite/',
                'twitch_category' => 'Halo Infinite',
            ],

            // ========================================
            // ROGUELIKES (Category Rep + Top Picks)
            // ========================================
            [
                'name' => 'Hades (2020)',
                'description' => 'Dungeon-crawling action with Greek mythology',
                'categories' => [CategoryFixtures::CATEGORY_ROGUELIKE, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1145360/Hades/',
                'epic_link' => 'https://store.epicgames.com/en-US/p/hades',
                'twitch_category' => 'Hades',
                'is_representative' => true, // Roguelike category rep
            ],
            [
                'name' => 'The Binding of Isaac: Rebirth (2014)',
                'description' => 'Twisted roguelike dungeon crawler',
                'categories' => [CategoryFixtures::CATEGORY_ROGUELIKE, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/250900/The_Binding_of_Isaac_Rebirth/',
                'twitch_category' => 'The Binding of Isaac: Rebirth',
            ],
            [
                'name' => 'Enter the Gungeon (2016)',
                'description' => 'Bullet hell dungeon crawler',
                'categories' => [CategoryFixtures::CATEGORY_ROGUELIKE, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/311690/Enter_the_Gungeon/',
                'twitch_category' => 'Enter the Gungeon',
            ],
            [
                'name' => 'FTL: Faster Than Light (2012)',
                'description' => 'Spaceship roguelike strategy',
                'categories' => [CategoryFixtures::CATEGORY_ROGUELIKE, CategoryFixtures::CATEGORY_STRATEGY],
                'steam_link' => 'https://store.steampowered.com/app/212680/FTL_Faster_Than_Light/',
                'twitch_category' => 'FTL: Faster Than Light',
            ],
            [
                'name' => 'Nuclear Throne (2015)',
                'description' => 'Post-apocalyptic roguelike shooter',
                'categories' => [CategoryFixtures::CATEGORY_ROGUELIKE, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/242680/Nuclear_Throne/',
                'twitch_category' => 'Nuclear Throne',
            ],
            [
                'name' => 'Vampire Survivors (2022)',
                'description' => 'Auto-battler survival roguelike',
                'categories' => [CategoryFixtures::CATEGORY_ROGUELIKE, CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1794680/Vampire_Survivors/',
                'twitch_category' => 'Vampire Survivors',
            ],
            [
                'name' => 'Balatro (2024)',
                'description' => 'Poker roguelike deck builder',
                'categories' => [CategoryFixtures::CATEGORY_ROGUELIKE],
                'steam_link' => 'https://store.steampowered.com/app/2379780/Balatro/',
                'twitch_category' => 'Balatro',
            ],

            // ========================================
            // SURVIVAL GAMES (Category Rep + Top Picks)
            // ========================================
            [
                'name' => 'Minecraft (2011)',
                'description' => 'Build, mine, survive, create',
                'categories' => [CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => null, // Not on Steam (Microsoft store)
                'twitch_category' => 'Minecraft',
                'is_representative' => true, // Survival category rep
            ],
            [
                'name' => 'Subnautica (2018)',
                'description' => 'Underwater alien world survival',
                'categories' => [CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/264710/Subnautica/',
                'twitch_category' => 'Subnautica',
            ],
            [
                'name' => 'The Forest (2018)',
                'description' => 'Survive the cannibal-infested peninsula',
                'categories' => [CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/242760/The_Forest/',
                'twitch_category' => 'The Forest',
            ],
            [
                'name' => 'Sons of the Forest (2023)',
                'description' => 'Survive the horrors of the forest',
                'categories' => [CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1326470/Sons_of_the_Forest/',
                'twitch_category' => 'Sons of the Forest',
            ],
            [
                'name' => 'Valheim (2021)',
                'description' => 'Viking survival in Norse purgatory',
                'categories' => [CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/892970/Valheim/',
                'twitch_category' => 'Valheim',
            ],
            [
                'name' => 'Don\'t Starve Together (2016)',
                'description' => 'Uncompromising wilderness survival',
                'categories' => [CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/322330/Dont_Starve_Together/',
                'twitch_category' => 'Don\'t Starve Together',
            ],

            // ========================================
            // STRATEGY GAMES (Category Rep + Top Picks)
            // ========================================
            [
                'name' => 'StarCraft II (2010)',
                'description' => 'Legendary RTS esport',
                'categories' => [CategoryFixtures::CATEGORY_STRATEGY],
                'steam_link' => null, // Battle.net exclusive
                'twitch_category' => 'StarCraft II',
                'is_representative' => true, // Strategy category rep
            ],
            [
                'name' => 'Civilization VI (2016)',
                'description' => 'Build an empire to stand the test of time',
                'categories' => [CategoryFixtures::CATEGORY_STRATEGY],
                'steam_link' => 'https://store.steampowered.com/app/289070/Sid_Meiers_Civilization_VI/',
                'twitch_category' => 'Sid Meier\'s Civilization VI',
            ],
            [
                'name' => 'XCOM 2 (2016)',
                'description' => 'Turn-based tactical combat',
                'categories' => [CategoryFixtures::CATEGORY_STRATEGY, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/268500/XCOM_2/',
                'twitch_category' => 'XCOM 2',
            ],
            [
                'name' => 'Factorio (2020)',
                'description' => 'Build automated production lines',
                'categories' => [CategoryFixtures::CATEGORY_STRATEGY, CategoryFixtures::CATEGORY_SURVIVAL],
                'steam_link' => 'https://store.steampowered.com/app/427520/Factorio/',
                'twitch_category' => 'Factorio',
            ],
            [
                'name' => 'RimWorld (2018)',
                'description' => 'Colony management storytelling',
                'categories' => [CategoryFixtures::CATEGORY_STRATEGY, CategoryFixtures::CATEGORY_SURVIVAL],
                'steam_link' => 'https://store.steampowered.com/app/294100/RimWorld/',
                'twitch_category' => 'RimWorld',
            ],
            [
                'name' => 'Terraria (2011)',
                'description' => '2D sandbox adventure',
                'categories' => [CategoryFixtures::CATEGORY_STRATEGY, CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/105600/Terraria/',
                'twitch_category' => 'Terraria',
            ],

            // ========================================
            // HORROR GAMES (Beyond Resident Evil)
            // ========================================
            [
                'name' => 'Dead Space (2008)',
                'description' => 'Sci-fi horror in deep space',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1693980/Dead_Space/',
                'twitch_category' => 'Dead Space',
            ],
            [
                'name' => 'Dead Space 2 (2011)',
                'description' => 'Return to the Sprawl',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/47780/Dead_Space_2/',
                'twitch_category' => 'Dead Space 2',
            ],
            [
                'name' => 'Silent Hill 2 (2001)',
                'description' => 'Psychological horror masterpiece',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => null, // No official PC release
                'twitch_category' => 'Silent Hill 2',
            ],
            [
                'name' => 'Amnesia: The Dark Descent (2010)',
                'description' => 'Hide and survive in the darkness',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/57300/Amnesia_The_Dark_Descent/',
                'twitch_category' => 'Amnesia: The Dark Descent',
            ],
            [
                'name' => 'Outlast (2013)',
                'description' => 'First-person survival horror',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/238320/Outlast/',
                'twitch_category' => 'Outlast',
            ],
            [
                'name' => 'Alien: Isolation (2014)',
                'description' => 'Survive the perfect organism',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/214490/Alien_Isolation/',
                'twitch_category' => 'Alien: Isolation',
            ],
            [
                'name' => 'Phasmophobia (2020)',
                'description' => 'Co-op ghost hunting horror',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_SURVIVAL],
                'steam_link' => 'https://store.steampowered.com/app/739630/Phasmophobia/',
                'twitch_category' => 'Phasmophobia',
            ],
            [
                'name' => 'Little Nightmares (2017)',
                'description' => 'Atmospheric puzzle horror',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/424840/Little_Nightmares/',
                'twitch_category' => 'Little Nightmares',
            ],

            // ========================================
            // FINAL FANTASY SERIES (Top 5)
            // ========================================
            [
                'name' => 'Final Fantasy VII (1997)',
                'description' => 'Legendary JRPG masterpiece',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/39140/FINAL_FANTASY_VII/',
                'twitch_category' => 'Final Fantasy VII',
            ],
            [
                'name' => 'Final Fantasy VII Remake (2020)',
                'description' => 'Reimagined classic with modern gameplay',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1462040/FINAL_FANTASY_VII_REMAKE_INTERGRADE/',
                'epic_link' => 'https://store.epicgames.com/en-US/p/final-fantasy-vii-remake',
                'twitch_category' => 'Final Fantasy VII Remake',
            ],
            [
                'name' => 'Final Fantasy X (2001)',
                'description' => 'Story of love and sacrifice',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/359870/FINAL_FANTASY_XX2_HD_Remaster/',
                'twitch_category' => 'Final Fantasy X',
            ],
            [
                'name' => 'Final Fantasy XIV (2010)',
                'description' => 'Award-winning MMORPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_MOBA],
                'steam_link' => 'https://store.steampowered.com/app/39210/FINAL_FANTASY_XIV_Online/',
                'twitch_category' => 'Final Fantasy XIV Online',
            ],
            [
                'name' => 'Final Fantasy XVI (2023)',
                'description' => 'Action-focused modern entry',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/2515020/FINAL_FANTASY_XVI/',
                'twitch_category' => 'Final Fantasy XVI',
            ],

            // ========================================
            // OTHER SHOOTERS
            // ========================================
            [
                'name' => 'Titanfall 2 (2016)',
                'description' => 'Mech combat with parkour',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1237970/Titanfall_2/',
                'twitch_category' => 'Titanfall 2',
            ],
            [
                'name' => 'Valorant (2020)',
                'description' => 'Tactical 5v5 character shooter',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_BATTLE_ROYALE],
                'steam_link' => null, // Riot Games launcher
                'twitch_category' => 'VALORANT',
            ],
            [
                'name' => 'Apex Legends (2019)',
                'description' => 'Hero-based battle royale',
                'categories' => [CategoryFixtures::CATEGORY_BATTLE_ROYALE, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1172470/Apex_Legends/',
                'twitch_category' => 'Apex Legends',
            ],
            [
                'name' => 'Overwatch 2 (2022)',
                'description' => 'Team-based hero shooter',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_MOBA],
                'steam_link' => null, // Battle.net exclusive
                'twitch_category' => 'Overwatch 2',
            ],

            // ========================================
            // PLATFORMERS (Beyond Mario/Metroid)
            // ========================================
            [
                'name' => 'Celeste (2018)',
                'description' => 'Challenging precision platformer',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/504230/Celeste/',
                'twitch_category' => 'Celeste',
            ],
            [
                'name' => 'Sonic Mania (2017)',
                'description' => 'Classic Sonic reimagined',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/584400/Sonic_Mania/',
                'twitch_category' => 'Sonic Mania',
            ],
            [
                'name' => 'Crash Bandicoot N. Sane Trilogy (2017)',
                'description' => 'Remastered classic platformers',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/731490/Crash_Bandicoot_N_Sane_Trilogy/',
                'twitch_category' => 'Crash Bandicoot N. Sane Trilogy',
            ],

            // ========================================
            // OTHER RPGs
            // ========================================
            [
                'name' => 'Persona 5 Royal (2019)',
                'description' => 'Stylish JRPG about phantom thieves',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/1687950/Persona_5_Royal/',
                'twitch_category' => 'Persona 5 Royal',
            ],
            [
                'name' => 'NieR: Automata (2017)',
                'description' => 'Philosophical action RPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/524220/NieRAutomata/',
                'twitch_category' => 'NieR: Automata',
            ],
            [
                'name' => 'Baldur\'s Gate 3 (2023)',
                'description' => 'Epic D&D RPG adventure',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_STRATEGY],
                'steam_link' => 'https://store.steampowered.com/app/1086940/Baldurs_Gate_3/',
                'twitch_category' => 'Baldur\'s Gate 3',
            ],
            [
                'name' => 'Divinity: Original Sin 2 (2017)',
                'description' => 'Tactical RPG with deep systems',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_STRATEGY],
                'steam_link' => 'https://store.steampowered.com/app/435150/Divinity_Original_Sin_2__Definitive_Edition/',
                'twitch_category' => 'Divinity: Original Sin 2',
            ],

            // ========================================
            // PHASE 3: ADDITIONAL GAMES (~160 games)
            // ========================================

            // ========================================
            // HALO SERIES (Remaining)
            // ========================================
            [
                'name' => 'Halo: Reach (2010)',
                'description' => 'Noble Team\'s final stand',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1064220/Halo_The_Master_Chief_Collection/',
                'twitch_category' => 'Halo: The Master Chief Collection',
            ],
            [
                'name' => 'Halo 4 (2012)',
                'description' => '343 Industries takes the helm',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1064220/Halo_The_Master_Chief_Collection/',
                'twitch_category' => 'Halo: The Master Chief Collection',
            ],
            [
                'name' => 'Halo 5: Guardians (2015)',
                'description' => 'Fireteam Osiris vs Master Chief',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Xbox exclusive
                'twitch_category' => 'Halo 5: Guardians',
            ],

            // ========================================
            // CLASSIC/RETRO SHOOTERS (Remaining)
            // ========================================
            [
                'name' => 'Quake (1996)',
                'description' => 'Gothic FPS with brutal atmosphere',
                'categories' => [CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/2310/Quake/',
                'gog_link' => 'https://www.gog.com/game/quake_the_offering',
                'twitch_category' => 'Quake',
            ],
            [
                'name' => 'Quake III Arena (1999)',
                'description' => 'Pure multiplayer arena shooter',
                'categories' => [CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/2200/Quake_III_Arena/',
                'twitch_category' => 'Quake III Arena',
            ],
            [
                'name' => 'Duke Nukem 3D (1996)',
                'description' => 'Hail to the king, baby',
                'categories' => [CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/225140/Duke_Nukem_3D_20th_Anniversary_World_Tour/',
                'gog_link' => 'https://www.gog.com/game/duke_nukem_3d_atomic_edition',
                'twitch_category' => 'Duke Nukem 3D',
            ],
            [
                'name' => 'Wolfenstein 3D (1992)',
                'description' => 'Grandfather of FPS games',
                'categories' => [CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/2270/Wolfenstein_3D/',
                'gog_link' => 'https://www.gog.com/game/wolfenstein_3d',
                'twitch_category' => 'Wolfenstein 3D',
            ],
            [
                'name' => 'Wolfenstein: The New Order (2014)',
                'description' => 'Alternate history FPS',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/201810/Wolfenstein_The_New_Order/',
                'twitch_category' => 'Wolfenstein: The New Order',
            ],
            [
                'name' => 'Team Fortress 2 (2007)',
                'description' => 'Team-based multiplayer FPS',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/440/Team_Fortress_2/',
                'twitch_category' => 'Team Fortress 2',
            ],

            // ========================================
            // FAR CRY SERIES
            // ========================================
            [
                'name' => 'Far Cry 3 (2012)',
                'description' => 'Tropical island insanity',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/220240/Far_Cry_3/',
                'twitch_category' => 'Far Cry 3',
            ],
            [
                'name' => 'Far Cry 4 (2014)',
                'description' => 'Himalayan adventure',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/298110/Far_Cry_4/',
                'twitch_category' => 'Far Cry 4',
            ],
            [
                'name' => 'Far Cry 5 (2018)',
                'description' => 'Cult uprising in Montana',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/552520/Far_Cry_5/',
                'twitch_category' => 'Far Cry 5',
            ],
            [
                'name' => 'Far Cry 6 (2021)',
                'description' => 'Revolution in Yara',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/2369390/Far_Cry_6/',
                'epic_link' => 'https://store.epicgames.com/en-US/p/far-cry-6',
                'twitch_category' => 'Far Cry 6',
            ],

            // ========================================
            // BIOSHOCK SERIES
            // ========================================
            [
                'name' => 'BioShock (2007)',
                'description' => 'Would you kindly explore Rapture',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/7670/BioShock/',
                'twitch_category' => 'BioShock',
            ],
            [
                'name' => 'BioShock 2 (2010)',
                'description' => 'Return to Rapture as Big Daddy',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/8850/BioShock_2/',
                'twitch_category' => 'BioShock 2',
            ],
            [
                'name' => 'BioShock Infinite (2013)',
                'description' => 'Sky city Columbia',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/8870/BioShock_Infinite/',
                'twitch_category' => 'BioShock Infinite',
            ],

            // ========================================
            // BORDERLANDS SERIES
            // ========================================
            [
                'name' => 'Borderlands (2009)',
                'description' => 'Loot shooter on Pandora',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/8980/Borderlands/',
                'twitch_category' => 'Borderlands',
            ],
            [
                'name' => 'Borderlands 2 (2012)',
                'description' => 'Handsome Jack\'s reign',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/49520/Borderlands_2/',
                'twitch_category' => 'Borderlands 2',
            ],
            [
                'name' => 'Borderlands 3 (2019)',
                'description' => 'Vault hunting across galaxies',
                'categories' => [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/397540/Borderlands_3/',
                'epic_link' => 'https://store.epicgames.com/en-US/p/borderlands-3',
                'twitch_category' => 'Borderlands 3',
            ],

            // ========================================
            // FINAL FANTASY SERIES (Remaining)
            // ========================================
            [
                'name' => 'Final Fantasy VI (1994)',
                'description' => 'Opera house and world destruction',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/1173820/FINAL_FANTASY_VI/',
                'twitch_category' => 'Final Fantasy VI',
            ],
            [
                'name' => 'Final Fantasy VIII (1999)',
                'description' => 'SeeD mercenaries and time compression',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/39150/FINAL_FANTASY_VIII/',
                'twitch_category' => 'Final Fantasy VIII',
            ],
            [
                'name' => 'Final Fantasy IX (2000)',
                'description' => 'Return to fantasy roots',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/377840/FINAL_FANTASY_IX/',
                'twitch_category' => 'Final Fantasy IX',
            ],
            [
                'name' => 'Final Fantasy XII (2006)',
                'description' => 'Ivalice Alliance politics',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/595520/FINAL_FANTASY_XII_THE_ZODIAC_AGE/',
                'twitch_category' => 'Final Fantasy XII',
            ],
            [
                'name' => 'Final Fantasy XIII (2009)',
                'description' => 'Lightning\'s story begins',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/292120/FINAL_FANTASY_XIII/',
                'twitch_category' => 'Final Fantasy XIII',
            ],
            [
                'name' => 'Final Fantasy XV (2016)',
                'description' => 'Road trip with the boys',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/637650/FINAL_FANTASY_XV_WINDOWS_EDITION/',
                'twitch_category' => 'Final Fantasy XV',
            ],

            // ========================================
            // DRAGON QUEST SERIES
            // ========================================
            [
                'name' => 'Dragon Quest XI (2017)',
                'description' => 'Epic JRPG adventure',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/742120/DRAGON_QUEST_XI_S_Echoes_of_an_Elusive_Age__Definitive_Edition/',
                'twitch_category' => 'Dragon Quest XI',
            ],
            [
                'name' => 'Dragon Quest VIII (2004)',
                'description' => 'Journey of the Cursed King',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => null, // Mobile/console only
                'twitch_category' => 'Dragon Quest VIII',
            ],
            [
                'name' => 'Dragon Quest III (1988)',
                'description' => 'Classic NES RPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_RETRO_SHOOTER],
                'steam_link' => null, // Various platforms
                'twitch_category' => 'Dragon Quest III',
            ],

            // ========================================
            // ELDER SCROLLS SERIES
            // ========================================
            [
                'name' => 'The Elder Scrolls III: Morrowind (2002)',
                'description' => 'Alien landscapes of Vvardenfell',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/22320/The_Elder_Scrolls_III_Morrowind_Game_of_the_Year_Edition/',
                'gog_link' => 'https://www.gog.com/game/the_elder_scrolls_iii_morrowind_goty_edition',
                'twitch_category' => 'The Elder Scrolls III: Morrowind',
            ],
            [
                'name' => 'The Elder Scrolls IV: Oblivion (2006)',
                'description' => 'Crisis in Cyrodiil',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/22330/The_Elder_Scrolls_IV_Oblivion_Game_of_the_Year_Edition/',
                'gog_link' => 'https://www.gog.com/game/elder_scrolls_iv_oblivion_game_of_the_year_edition_deluxe_the',
                'twitch_category' => 'The Elder Scrolls IV: Oblivion',
            ],
            [
                'name' => 'The Elder Scrolls V: Skyrim (2011)',
                'description' => 'Dragonborn awakens',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/489830/The_Elder_Scrolls_V_Skyrim_Special_Edition/',
                'twitch_category' => 'The Elder Scrolls V: Skyrim',
            ],
            [
                'name' => 'The Elder Scrolls Online (2014)',
                'description' => 'MMORPG in Tamriel',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_MOBA, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/306130/The_Elder_Scrolls_Online/',
                'twitch_category' => 'The Elder Scrolls Online',
            ],

            // ========================================
            // FALLOUT SERIES
            // ========================================
            [
                'name' => 'Fallout (1997)',
                'description' => 'Post-nuclear RPG classic',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/38400/Fallout_A_Post_Nuclear_Role_Playing_Game/',
                'gog_link' => 'https://www.gog.com/game/fallout',
                'twitch_category' => 'Fallout',
            ],
            [
                'name' => 'Fallout 2 (1998)',
                'description' => 'Chosen One saves the wasteland',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/38410/Fallout_2_A_Post_Nuclear_Role_Playing_Game/',
                'gog_link' => 'https://www.gog.com/game/fallout_2',
                'twitch_category' => 'Fallout 2',
            ],
            [
                'name' => 'Fallout 3 (2008)',
                'description' => 'Capital Wasteland exploration',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/22300/Fallout_3/',
                'gog_link' => 'https://www.gog.com/game/fallout_3_game_of_the_year_edition',
                'twitch_category' => 'Fallout 3',
            ],
            [
                'name' => 'Fallout: New Vegas (2010)',
                'description' => 'Mojave Wasteland RPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/22380/Fallout_New_Vegas/',
                'gog_link' => 'https://www.gog.com/game/fallout_new_vegas_ultimate_edition',
                'twitch_category' => 'Fallout: New Vegas',
            ],
            [
                'name' => 'Fallout 4 (2015)',
                'description' => 'Commonwealth settlement building',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/377160/Fallout_4/',
                'twitch_category' => 'Fallout 4',
            ],

            // ========================================
            // MASS EFFECT SERIES
            // ========================================
            [
                'name' => 'Mass Effect (2007)',
                'description' => 'Space opera RPG begins',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1328670/Mass_Effect_Legendary_Edition/',
                'twitch_category' => 'Mass Effect',
            ],
            [
                'name' => 'Mass Effect 2 (2010)',
                'description' => 'Suicide mission masterpiece',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1328670/Mass_Effect_Legendary_Edition/',
                'twitch_category' => 'Mass Effect 2',
            ],
            [
                'name' => 'Mass Effect 3 (2012)',
                'description' => 'Battle for Earth',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1328670/Mass_Effect_Legendary_Edition/',
                'twitch_category' => 'Mass Effect 3',
            ],
            [
                'name' => 'Mass Effect: Andromeda (2017)',
                'description' => 'New galaxy exploration',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/1238000/Mass_Effect_Andromeda/',
                'twitch_category' => 'Mass Effect: Andromeda',
            ],

            // ========================================
            // OTHER CLASSIC RPGs
            // ========================================
            [
                'name' => 'Chrono Trigger (1995)',
                'description' => 'Time-traveling JRPG masterpiece',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/613830/CHRONO_TRIGGER/',
                'twitch_category' => 'Chrono Trigger',
            ],
            [
                'name' => 'Secret of Mana (1993)',
                'description' => 'Action RPG with co-op',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Various platforms
                'twitch_category' => 'Secret of Mana',
            ],
            [
                'name' => 'EarthBound (1994)',
                'description' => 'Quirky modern-day RPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => null, // Nintendo exclusive
                'twitch_category' => 'EarthBound',
            ],
            [
                'name' => 'Baldur\'s Gate (1998)',
                'description' => 'Infinity Engine D&D RPG',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_STRATEGY],
                'steam_link' => 'https://store.steampowered.com/app/228280/Baldurs_Gate_Enhanced_Edition/',
                'gog_link' => 'https://www.gog.com/game/baldurs_gate_enhanced_edition',
                'twitch_category' => 'Baldur\'s Gate',
            ],
            [
                'name' => 'Baldur\'s Gate II (2000)',
                'description' => 'Epic sequel in Amn',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_STRATEGY],
                'steam_link' => 'https://store.steampowered.com/app/257350/Baldurs_Gate_II_Enhanced_Edition/',
                'gog_link' => 'https://www.gog.com/game/baldurs_gate_2_enhanced_edition',
                'twitch_category' => 'Baldur\'s Gate II',
            ],
            [
                'name' => 'The Witcher 2: Assassins of Kings (2011)',
                'description' => 'Geralt\'s political intrigue',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/20920/The_Witcher_2_Assassins_of_Kings_Enhanced_Edition/',
                'gog_link' => 'https://www.gog.com/game/the_witcher_2',
                'twitch_category' => 'The Witcher 2: Assassins of Kings',
            ],
            [
                'name' => 'Persona 3 (2006)',
                'description' => 'Dark hour and social links',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => null, // Console/remaster only
                'twitch_category' => 'Persona 3',
            ],
            [
                'name' => 'Persona 4 (2008)',
                'description' => 'Murder mystery in small town',
                'categories' => [CategoryFixtures::CATEGORY_RPG],
                'steam_link' => 'https://store.steampowered.com/app/1113000/Persona_4_Golden/',
                'twitch_category' => 'Persona 4 Golden',
            ],
            [
                'name' => 'Kingdom Hearts (2002)',
                'description' => 'Disney meets Final Fantasy',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/2552430/KINGDOM_HEARTS_HD_15_25_ReMIX/',
                'epic_link' => 'https://store.epicgames.com/en-US/p/kingdom-hearts-hd-1-5-2-5-remix',
                'twitch_category' => 'Kingdom Hearts',
            ],
            [
                'name' => 'Kingdom Hearts II (2005)',
                'description' => 'Organization XIII saga',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/2552430/KINGDOM_HEARTS_HD_15_25_ReMIX/',
                'epic_link' => 'https://store.epicgames.com/en-US/p/kingdom-hearts-hd-1-5-2-5-remix',
                'twitch_category' => 'Kingdom Hearts II',
            ],
            [
                'name' => 'Monster Hunter: World (2018)',
                'description' => 'Hunt massive beasts',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/582010/Monster_Hunter_World/',
                'twitch_category' => 'Monster Hunter: World',
            ],
            [
                'name' => 'Monster Hunter Rise (2021)',
                'description' => 'Wirebug mobility hunting',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1446780/Monster_Hunter_Rise/',
                'twitch_category' => 'Monster Hunter Rise',
            ],
            [
                'name' => 'Diablo II (2000)',
                'description' => 'Action RPG legend',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Battle.net exclusive
                'twitch_category' => 'Diablo II',
            ],
            [
                'name' => 'Diablo III (2012)',
                'description' => 'Demon-slaying loot fest',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Battle.net exclusive
                'twitch_category' => 'Diablo III',
            ],
            [
                'name' => 'Diablo IV (2023)',
                'description' => 'Return to darkness',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/2344520/Diablo_IV/',
                'twitch_category' => 'Diablo IV',
            ],
            [
                'name' => 'Path of Exile (2013)',
                'description' => 'Deep ARPG systems',
                'categories' => [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/238960/Path_of_Exile/',
                'twitch_category' => 'Path of Exile',
            ],

            // ========================================
            // SONIC SERIES
            // ========================================
            [
                'name' => 'Sonic the Hedgehog (1991)',
                'description' => 'Blue blur\'s debut',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/71113/Sonic_the_Hedgehog/',
                'twitch_category' => 'Sonic the Hedgehog',
            ],
            [
                'name' => 'Sonic the Hedgehog 2 (1992)',
                'description' => 'Tails joins the adventure',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/71163/Sonic_the_Hedgehog_2/',
                'twitch_category' => 'Sonic the Hedgehog 2',
            ],
            [
                'name' => 'Sonic the Hedgehog 3 (1994)',
                'description' => 'Knuckles debut and epic finale',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/71162/Sonic_3__Knuckles/',
                'twitch_category' => 'Sonic the Hedgehog 3',
            ],
            [
                'name' => 'Sonic Frontiers (2022)',
                'description' => 'Open-zone Sonic adventure',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/1237320/Sonic_Frontiers/',
                'twitch_category' => 'Sonic Frontiers',
            ],

            // ========================================
            // DONKEY KONG SERIES
            // ========================================
            [
                'name' => 'Donkey Kong Country (1994)',
                'description' => 'Revolutionary pre-rendered graphics',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Nintendo exclusive
                'twitch_category' => 'Donkey Kong Country',
            ],
            [
                'name' => 'Donkey Kong Country 2 (1995)',
                'description' => 'Diddy and Dixie save DK',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Nintendo exclusive
                'twitch_category' => 'Donkey Kong Country 2',
            ],
            [
                'name' => 'Donkey Kong Country: Tropical Freeze (2014)',
                'description' => 'Modern DK platforming',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Nintendo exclusive
                'twitch_category' => 'Donkey Kong Country: Tropical Freeze',
            ],
            [
                'name' => 'Donkey Kong 64 (1999)',
                'description' => '3D collect-a-thon',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => null, // Nintendo exclusive
                'twitch_category' => 'Donkey Kong 64',
            ],

            // ========================================
            // KIRBY SERIES
            // ========================================
            [
                'name' => 'Kirby Super Star (1996)',
                'description' => 'Multiple adventures in one',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Nintendo exclusive
                'twitch_category' => 'Kirby Super Star',
            ],
            [
                'name' => 'Kirby\'s Dream Land (1992)',
                'description' => 'Pink puffball debuts',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Nintendo exclusive
                'twitch_category' => 'Kirby\'s Dream Land',
            ],
            [
                'name' => 'Kirby and the Forgotten Land (2022)',
                'description' => '3D Kirby adventure',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => null, // Nintendo exclusive
                'twitch_category' => 'Kirby and the Forgotten Land',
            ],

            // ========================================
            // CRASH BANDICOOT SERIES (Remaining)
            // ========================================
            [
                'name' => 'Crash Bandicoot (1996)',
                'description' => 'PS1 platforming icon',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/731490/Crash_Bandicoot_N_Sane_Trilogy/',
                'twitch_category' => 'Crash Bandicoot',
            ],
            [
                'name' => 'Crash Bandicoot 2: Cortex Strikes Back (1997)',
                'description' => 'Warp room adventures',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/731490/Crash_Bandicoot_N_Sane_Trilogy/',
                'twitch_category' => 'Crash Bandicoot 2: Cortex Strikes Back',
            ],
            [
                'name' => 'Crash Bandicoot: Warped (1998)',
                'description' => 'Time-traveling platforming',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/731490/Crash_Bandicoot_N_Sane_Trilogy/',
                'twitch_category' => 'Crash Bandicoot: Warped',
            ],
            [
                'name' => 'Crash Bandicoot 4: It\'s About Time (2020)',
                'description' => 'Modern sequel to trilogy',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1446770/Crash_Bandicoot_4_Its_About_Time/',
                'twitch_category' => 'Crash Bandicoot 4: It\'s About Time',
            ],

            // ========================================
            // SPYRO SERIES
            // ========================================
            [
                'name' => 'Spyro the Dragon (1998)',
                'description' => 'Purple dragon collects gems',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/996580/Spyro_Reignited_Trilogy/',
                'twitch_category' => 'Spyro the Dragon',
            ],
            [
                'name' => 'Spyro 2: Ripto\'s Rage! (1999)',
                'description' => 'New worlds to explore',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/996580/Spyro_Reignited_Trilogy/',
                'twitch_category' => 'Spyro 2: Ripto\'s Rage!',
            ],
            [
                'name' => 'Spyro: Year of the Dragon (2000)',
                'description' => 'Dragon eggs stolen',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/996580/Spyro_Reignited_Trilogy/',
                'twitch_category' => 'Spyro: Year of the Dragon',
            ],

            // ========================================
            // OTHER PLATFORMERS
            // ========================================
            [
                'name' => 'Rayman Legends (2013)',
                'description' => 'Musical platforming excellence',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/242550/Rayman_Legends/',
                'twitch_category' => 'Rayman Legends',
            ],
            [
                'name' => 'A Hat in Time (2017)',
                'description' => '3D platformer collectathon',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/253230/A_Hat_in_Time/',
                'twitch_category' => 'A Hat in Time',
            ],
            [
                'name' => 'Psychonauts (2005)',
                'description' => 'Mind-bending adventure',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/3830/Psychonauts/',
                'gog_link' => 'https://www.gog.com/game/psychonauts',
                'twitch_category' => 'Psychonauts',
            ],
            [
                'name' => 'Psychonauts 2 (2021)',
                'description' => 'Return to mental worlds',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/607080/Psychonauts_2/',
                'twitch_category' => 'Psychonauts 2',
            ],
            [
                'name' => 'Ratchet & Clank (2002)',
                'description' => 'Lombax and robot duo',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // PlayStation exclusive
                'twitch_category' => 'Ratchet & Clank',
            ],
            [
                'name' => 'Jak and Daxter (2001)',
                'description' => 'PS2 platforming classic',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => null, // PlayStation exclusive
                'twitch_category' => 'Jak and Daxter: The Precursor Legacy',
            ],
            [
                'name' => 'Banjo-Kazooie (1998)',
                'description' => 'N64 collect-a-thon classic',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => null, // Xbox/Rare Replay
                'twitch_category' => 'Banjo-Kazooie',
            ],
            [
                'name' => 'Banjo-Tooie (2000)',
                'description' => 'Bigger interconnected worlds',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => null, // Xbox/Rare Replay
                'twitch_category' => 'Banjo-Tooie',
            ],

            // ========================================
            // ROGUELIKE GAMES (Remaining)
            // ========================================
            [
                'name' => 'Hades II (2024)',
                'description' => 'Sequel to acclaimed roguelike',
                'categories' => [CategoryFixtures::CATEGORY_ROGUELIKE, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1145350/Hades_II/',
                'twitch_category' => 'Hades II',
            ],
            [
                'name' => 'Into the Breach (2018)',
                'description' => 'Turn-based mech strategy',
                'categories' => [CategoryFixtures::CATEGORY_ROGUELIKE, CategoryFixtures::CATEGORY_STRATEGY],
                'steam_link' => 'https://store.steampowered.com/app/590380/Into_the_Breach/',
                'twitch_category' => 'Into the Breach',
            ],
            [
                'name' => 'Rogue Legacy (2013)',
                'description' => 'Hereditary roguelike castle',
                'categories' => [CategoryFixtures::CATEGORY_ROGUELIKE, CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/241600/Rogue_Legacy/',
                'twitch_category' => 'Rogue Legacy',
            ],
            [
                'name' => 'Rogue Legacy 2 (2022)',
                'description' => 'Expanded dynasty continues',
                'categories' => [CategoryFixtures::CATEGORY_ROGUELIKE, CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1253920/Rogue_Legacy_2/',
                'twitch_category' => 'Rogue Legacy 2',
            ],

            // ========================================
            // HORROR GAMES (Remaining)
            // ========================================
            [
                'name' => 'Silent Hill (1999)',
                'description' => 'Foggy psychological horror',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => null, // No official PC release
                'twitch_category' => 'Silent Hill',
            ],
            [
                'name' => 'Silent Hill 3 (2003)',
                'description' => 'Heather\'s nightmare continues',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/19000/Silent_Hill_3/',
                'gog_link' => 'https://www.gog.com/game/silent_hill_3',
                'twitch_category' => 'Silent Hill 3',
            ],
            [
                'name' => 'Outlast 2 (2017)',
                'description' => 'Cult horror in Arizona',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/414700/Outlast_2/',
                'twitch_category' => 'Outlast 2',
            ],
            [
                'name' => 'SOMA (2015)',
                'description' => 'Underwater existential horror',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/282140/SOMA/',
                'gog_link' => 'https://www.gog.com/game/soma',
                'twitch_category' => 'SOMA',
            ],
            [
                'name' => 'Layers of Fear (2016)',
                'description' => 'Painter\'s madness',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/391720/Layers_of_Fear/',
                'twitch_category' => 'Layers of Fear',
            ],
            [
                'name' => 'The Evil Within (2014)',
                'description' => 'Shinji Mikami\'s survival horror',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/268050/The_Evil_Within/',
                'twitch_category' => 'The Evil Within',
            ],
            [
                'name' => 'The Evil Within 2 (2017)',
                'description' => 'Return to STEM',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/601430/The_Evil_Within_2/',
                'twitch_category' => 'The Evil Within 2',
            ],
            [
                'name' => 'Dead Space 3 (2013)',
                'description' => 'Co-op necromorph action',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // EA app exclusive
                'twitch_category' => 'Dead Space 3',
            ],
            [
                'name' => 'Dead Space (2023)',
                'description' => 'Remake of sci-fi horror classic',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1693980/Dead_Space/',
                'twitch_category' => 'Dead Space',
            ],
            [
                'name' => 'Five Nights at Freddy\'s (2014)',
                'description' => 'Animatronic jump scares',
                'categories' => [CategoryFixtures::CATEGORY_HORROR],
                'steam_link' => 'https://store.steampowered.com/app/319510/Five_Nights_at_Freddys/',
                'twitch_category' => 'Five Nights at Freddy\'s',
            ],
            [
                'name' => 'Little Nightmares II (2021)',
                'description' => 'Atmospheric puzzle horror sequel',
                'categories' => [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/860510/Little_Nightmares_II/',
                'twitch_category' => 'Little Nightmares II',
            ],

            // ========================================
            // MOBA GAMES (Remaining)
            // ========================================
            [
                'name' => 'Mobile Legends (2016)',
                'description' => 'Mobile MOBA esport',
                'categories' => [CategoryFixtures::CATEGORY_MOBA],
                'steam_link' => null, // Mobile only
                'twitch_category' => 'Mobile Legends: Bang Bang',
            ],
            [
                'name' => 'Heroes of the Storm (2015)',
                'description' => 'Blizzard universe MOBA',
                'categories' => [CategoryFixtures::CATEGORY_MOBA],
                'steam_link' => null, // Battle.net exclusive
                'twitch_category' => 'Heroes of the Storm',
            ],
            [
                'name' => 'Smite (2014)',
                'description' => 'Third-person god MOBA',
                'categories' => [CategoryFixtures::CATEGORY_MOBA, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/386360/SMITE/',
                'twitch_category' => 'SMITE',
            ],

            // ========================================
            // FIGHTING GAMES
            // ========================================
            [
                'name' => 'Street Fighter II (1991)',
                'description' => 'Fighting game revolution',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/586200/Ultra_Street_Fighter_II_The_Final_Challengers/',
                'twitch_category' => 'Street Fighter II',
            ],
            [
                'name' => 'Street Fighter III: 3rd Strike (1999)',
                'description' => 'Parry system masterpiece',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/586200/Street_Fighter_III_3rd_Strike/',
                'twitch_category' => 'Street Fighter III: 3rd Strike',
            ],
            [
                'name' => 'Street Fighter IV (2008)',
                'description' => 'Fighting game renaissance',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/45760/Ultra_Street_Fighter_IV/',
                'twitch_category' => 'Ultra Street Fighter IV',
            ],
            [
                'name' => 'Street Fighter V (2016)',
                'description' => 'Esports fighting game',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/310950/Street_Fighter_V/',
                'twitch_category' => 'Street Fighter V',
            ],
            [
                'name' => 'Street Fighter 6 (2023)',
                'description' => 'Modern fighting with Drive system',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1364780/Street_Fighter_6/',
                'twitch_category' => 'Street Fighter 6',
            ],
            [
                'name' => 'Mortal Kombat (1992)',
                'description' => 'Brutal fighting game original',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Various compilations
                'twitch_category' => 'Mortal Kombat',
            ],
            [
                'name' => 'Mortal Kombat II (1993)',
                'description' => 'Arcade violence amplified',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Various compilations
                'twitch_category' => 'Mortal Kombat II',
            ],
            [
                'name' => 'Mortal Kombat 11 (2019)',
                'description' => 'Time-bending brutality',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/976310/Mortal_Kombat11/',
                'twitch_category' => 'Mortal Kombat 11',
            ],
            [
                'name' => 'Mortal Kombat 1 (2023)',
                'description' => 'Timeline reboot',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1971870/Mortal_Kombat_1/',
                'twitch_category' => 'Mortal Kombat 1',
            ],
            [
                'name' => 'Tekken 3 (1997)',
                'description' => 'Arcade fighting perfection',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // No official PC release
                'twitch_category' => 'Tekken 3',
            ],
            [
                'name' => 'Tekken 5 (2004)',
                'description' => 'Return to form',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // No official PC release
                'twitch_category' => 'Tekken 5',
            ],
            [
                'name' => 'Tekken 7 (2015)',
                'description' => 'Mishima saga conclusion',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/389730/TEKKEN_7/',
                'twitch_category' => 'Tekken 7',
            ],
            [
                'name' => 'Tekken 8 (2024)',
                'description' => 'New generation of Iron Fist',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1778820/TEKKEN_8/',
                'twitch_category' => 'Tekken 8',
            ],
            [
                'name' => 'Super Smash Bros. Melee (2001)',
                'description' => 'Competitive platform fighter',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Nintendo exclusive
                'twitch_category' => 'Super Smash Bros. Melee',
            ],
            [
                'name' => 'Super Smash Bros. Brawl (2008)',
                'description' => 'Story mode and online',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Nintendo exclusive
                'twitch_category' => 'Super Smash Bros. Brawl',
            ],
            [
                'name' => 'Super Smash Bros. Ultimate (2018)',
                'description' => 'Everyone is here',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Nintendo exclusive
                'twitch_category' => 'Super Smash Bros. Ultimate',
            ],
            [
                'name' => 'Marvel vs. Capcom 2 (2000)',
                'description' => 'Hyper fighting crossover',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Delisted
                'twitch_category' => 'Marvel vs. Capcom 2',
            ],
            [
                'name' => 'Marvel vs. Capcom 3 (2011)',
                'description' => 'HD tag team battles',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/357190/Ultimate_Marvel_vs_Capcom_3/',
                'twitch_category' => 'Ultimate Marvel vs. Capcom 3',
            ],
            [
                'name' => 'Dragon Ball FighterZ (2018)',
                'description' => 'Anime fighter perfection',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/678950/DRAGON_BALL_FighterZ/',
                'twitch_category' => 'Dragon Ball FighterZ',
            ],
            [
                'name' => 'Guilty Gear Strive (2021)',
                'description' => 'Modern anime fighter',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/1384160/GUILTY_GEAR_STRIVE/',
                'twitch_category' => 'Guilty Gear -Strive-',
            ],
            [
                'name' => 'Skullgirls (2012)',
                'description' => 'Hand-drawn fighting game',
                'categories' => [CategoryFixtures::CATEGORY_FIGHTING, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/245170/Skullgirls_2nd_Encore/',
                'twitch_category' => 'Skullgirls',
            ],

            // ========================================
            // STRATEGY/SIMULATION GAMES (Remaining)
            // ========================================
            [
                'name' => 'Civilization V (2010)',
                'description' => 'One more turn classic',
                'categories' => [CategoryFixtures::CATEGORY_STRATEGY],
                'steam_link' => 'https://store.steampowered.com/app/8930/Sid_Meiers_Civilization_V/',
                'twitch_category' => 'Sid Meier\'s Civilization V',
            ],
            [
                'name' => 'StarCraft (1998)',
                'description' => 'RTS that defined esports',
                'categories' => [CategoryFixtures::CATEGORY_STRATEGY],
                'steam_link' => null, // Battle.net exclusive (free)
                'twitch_category' => 'StarCraft',
            ],
            [
                'name' => 'Age of Empires II (1999)',
                'description' => 'Medieval RTS classic',
                'categories' => [CategoryFixtures::CATEGORY_STRATEGY],
                'steam_link' => 'https://store.steampowered.com/app/813780/Age_of_Empires_II_Definitive_Edition/',
                'twitch_category' => 'Age of Empires II',
            ],
            [
                'name' => 'Age of Empires IV (2021)',
                'description' => 'Modern RTS revival',
                'categories' => [CategoryFixtures::CATEGORY_STRATEGY],
                'steam_link' => 'https://store.steampowered.com/app/1466860/Age_of_Empires_IV/',
                'twitch_category' => 'Age of Empires IV',
            ],
            [
                'name' => 'Total War: Warhammer II (2017)',
                'description' => 'Epic fantasy battles',
                'categories' => [CategoryFixtures::CATEGORY_STRATEGY],
                'steam_link' => 'https://store.steampowered.com/app/594570/Total_War_WARHAMMER_II/',
                'twitch_category' => 'Total War: Warhammer II',
            ],
            [
                'name' => 'Total War: Warhammer III (2022)',
                'description' => 'Immortal Empires combined map',
                'categories' => [CategoryFixtures::CATEGORY_STRATEGY],
                'steam_link' => 'https://store.steampowered.com/app/1142710/Total_War_WARHAMMER_III/',
                'twitch_category' => 'Total War: Warhammer III',
            ],
            [
                'name' => 'Cities: Skylines (2015)',
                'description' => 'Modern city builder',
                'categories' => [CategoryFixtures::CATEGORY_STRATEGY],
                'steam_link' => 'https://store.steampowered.com/app/255710/Cities_Skylines/',
                'twitch_category' => 'Cities: Skylines',
            ],
            [
                'name' => 'Oxygen Not Included (2019)',
                'description' => 'Space colony survival sim',
                'categories' => [CategoryFixtures::CATEGORY_STRATEGY, CategoryFixtures::CATEGORY_SURVIVAL],
                'steam_link' => 'https://store.steampowered.com/app/457140/Oxygen_Not_Included/',
                'twitch_category' => 'Oxygen Not Included',
            ],
            [
                'name' => 'Dwarf Fortress (2022)',
                'description' => 'Legendary colony sim',
                'categories' => [CategoryFixtures::CATEGORY_STRATEGY, CategoryFixtures::CATEGORY_SURVIVAL],
                'steam_link' => 'https://store.steampowered.com/app/975370/Dwarf_Fortress/',
                'twitch_category' => 'Dwarf Fortress',
            ],
            [
                'name' => 'Satisfactory (2024)',
                'description' => 'First-person factory builder',
                'categories' => [CategoryFixtures::CATEGORY_STRATEGY, CategoryFixtures::CATEGORY_SURVIVAL],
                'steam_link' => 'https://store.steampowered.com/app/526870/Satisfactory/',
                'epic_link' => 'https://store.epicgames.com/en-US/p/satisfactory',
                'twitch_category' => 'Satisfactory',
            ],

            // ========================================
            // SURVIVAL/CRAFTING GAMES (Remaining)
            // ========================================
            [
                'name' => 'Rust (2018)',
                'description' => 'Brutal multiplayer survival',
                'categories' => [CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/252490/Rust/',
                'twitch_category' => 'Rust',
            ],
            [
                'name' => 'ARK: Survival Evolved (2017)',
                'description' => 'Dinosaur survival sandbox',
                'categories' => [CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/346110/ARK_Survival_Evolved/',
                'twitch_category' => 'ARK: Survival Evolved',
            ],
            [
                'name' => 'Subnautica: Below Zero (2021)',
                'description' => 'Arctic planet survival',
                'categories' => [CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/848450/Subnautica_Below_Zero/',
                'twitch_category' => 'Subnautica: Below Zero',
            ],
            [
                'name' => 'Palworld (2024)',
                'description' => 'Creature catching survival',
                'categories' => [CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_OPEN_WORLD],
                'steam_link' => 'https://store.steampowered.com/app/1623730/Palworld/',
                'twitch_category' => 'Palworld',
            ],
            [
                'name' => 'Raft (2022)',
                'description' => 'Ocean survival on floating debris',
                'categories' => [CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/648800/Raft/',
                'twitch_category' => 'Raft',
            ],
            [
                'name' => 'Grounded (2022)',
                'description' => 'Backyard survival at ant size',
                'categories' => [CategoryFixtures::CATEGORY_SURVIVAL, CategoryFixtures::CATEGORY_ACTION, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/962130/Grounded/',
                'twitch_category' => 'Grounded',
            ],

            // ========================================
            // RETRO CLASSICS (NES)
            // ========================================
            [
                'name' => 'Mega Man 2 (1988)',
                'description' => 'Blue Bomber\'s best',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Various collections
                'twitch_category' => 'Mega Man 2',
            ],
            [
                'name' => 'Mega Man 3 (1990)',
                'description' => 'Slide and Rush debut',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Various collections
                'twitch_category' => 'Mega Man 3',
            ],
            [
                'name' => 'Castlevania (1986)',
                'description' => 'Whip-cracking vampire hunter',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Various collections
                'twitch_category' => 'Castlevania',
            ],
            [
                'name' => 'Castlevania III: Dracula\'s Curse (1989)',
                'description' => 'Multiple paths and characters',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Various collections
                'twitch_category' => 'Castlevania III: Dracula\'s Curse',
            ],
            [
                'name' => 'Ninja Gaiden (1988)',
                'description' => 'Cinematic ninja action',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Various collections
                'twitch_category' => 'Ninja Gaiden',
            ],
            [
                'name' => 'Contra (1987)',
                'description' => 'Run and gun co-op classic',
                'categories' => [CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Various collections
                'twitch_category' => 'Contra',
            ],
            [
                'name' => 'Battletoads (1991)',
                'description' => 'Brutally difficult beat-em-up',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Rare Replay
                'twitch_category' => 'Battletoads',
            ],

            // ========================================
            // RETRO CLASSICS (SNES)
            // ========================================
            [
                'name' => 'Super Castlevania IV (1991)',
                'description' => '16-bit whip mastery',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Various collections
                'twitch_category' => 'Super Castlevania IV',
            ],
            [
                'name' => 'Castlevania: Symphony of the Night (1997)',
                'description' => 'Metroidvania masterpiece',
                'categories' => [CategoryFixtures::CATEGORY_METROIDVANIA, CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Various platforms
                'twitch_category' => 'Castlevania: Symphony of the Night',
            ],
            [
                'name' => 'Super Metroid (1994)',
                'description' => 'Defining metroidvania',
                'categories' => [CategoryFixtures::CATEGORY_METROIDVANIA, CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Nintendo exclusive
                'twitch_category' => 'Super Metroid',
            ],
            [
                'name' => 'F-Zero (1990)',
                'description' => 'Futuristic racing',
                'categories' => [CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => null, // Nintendo exclusive
                'twitch_category' => 'F-Zero',
            ],

            // ========================================
            // RETRO CLASSICS (Other)
            // ========================================
            [
                'name' => 'Sonic CD (1993)',
                'description' => 'Time-traveling Sonic',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/200940/Sonic_CD/',
                'twitch_category' => 'Sonic CD',
            ],
            [
                'name' => 'Streets of Rage 2 (1992)',
                'description' => 'Genesis beat-em-up perfection',
                'categories' => [CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/71164/Streets_of_Rage_2/',
                'twitch_category' => 'Streets of Rage 2',
            ],
            [
                'name' => 'Comix Zone (1995)',
                'description' => 'Comic book beat-em-up',
                'categories' => [CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/34285/Comix_Zone/',
                'twitch_category' => 'Comix Zone',
            ],
            [
                'name' => 'Shinobi III (1993)',
                'description' => 'Ninja action masterpiece',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_RETRO_SHOOTER, CategoryFixtures::CATEGORY_ACTION],
                'steam_link' => 'https://store.steampowered.com/app/34279/Shinobi_III_Return_of_the_Ninja_Master/',
                'twitch_category' => 'Shinobi III: Return of the Ninja Master',
            ],

            // ========================================
            // PUZZLE GAMES
            // ========================================
            [
                'name' => 'Tetris Effect (2018)',
                'description' => 'Synesthetic Tetris experience',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER], // Note: No Puzzle category yet
                'steam_link' => 'https://store.steampowered.com/app/1003590/Tetris_Effect_Connected/',
                'epic_link' => 'https://store.epicgames.com/en-US/p/tetris-effect-connected',
                'twitch_category' => 'Tetris Effect: Connected',
            ],
            [
                'name' => 'The Witness (2016)',
                'description' => 'Island of perspective puzzles',
                'categories' => [CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/210970/The_Witness/',
                'gog_link' => 'https://www.gog.com/game/the_witness',
                'twitch_category' => 'The Witness',
            ],
            [
                'name' => 'Baba Is You (2019)',
                'description' => 'Rule-manipulating puzzler',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER],
                'steam_link' => 'https://store.steampowered.com/app/736260/Baba_Is_You/',
                'twitch_category' => 'Baba Is You',
            ],
            [
                'name' => 'Stephen\'s Sausage Roll (2016)',
                'description' => 'Brutally difficult sokoban',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER],
                'steam_link' => 'https://store.steampowered.com/app/353540/Stephens_Sausage_Roll/',
                'twitch_category' => 'Stephen\'s Sausage Roll',
            ],
            [
                'name' => 'Braid (2008)',
                'description' => 'Time manipulation puzzler',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/26800/Braid/',
                'gog_link' => 'https://www.gog.com/game/braid',
                'twitch_category' => 'Braid',
            ],
            [
                'name' => 'Fez (2012)',
                'description' => '2D/3D perspective rotation',
                'categories' => [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_ADVENTURE],
                'steam_link' => 'https://store.steampowered.com/app/224760/FEZ/',
                'gog_link' => 'https://www.gog.com/game/fez',
                'twitch_category' => 'Fez',
            ],
        ];
    }
}
