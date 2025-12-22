<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add randomizer-friendly games and boomer shooters with store links
 */
final class Version20251222215000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add classic franchises, randomizer games, and boomer shooters with store links';
    }

    public function up(Schema $schema): void
    {
        // Format: [name, description, steam, epic, gog, twitch, [categories]]
        $games = [
            // RESIDENT EVIL FRANCHISE
            ['Resident Evil', 'Survival horror classic with randomizer support', 'https://store.steampowered.com/app/304240/', null, 'https://www.gog.com/game/resident_evil', 'Resident Evil', ['Horror', 'Survival', 'Action', 'Story-Rich', 'Randomizer']],
            ['Resident Evil 2', 'Remake of the survival horror classic', 'https://store.steampowered.com/app/883710/', null, null, 'Resident Evil 2', ['Horror', 'Survival', 'Action', 'Story-Rich', 'Zombies', 'Randomizer']],
            ['Resident Evil 3', 'Remake featuring Nemesis', 'https://store.steampowered.com/app/952060/', null, null, 'Resident Evil 3', ['Horror', 'Survival', 'Action', 'Zombies', 'Randomizer']],
            ['Resident Evil 7', 'First-person survival horror', 'https://store.steampowered.com/app/418370/', null, null, 'Resident Evil 7: Biohazard', ['Horror', 'Survival', 'FPS', 'Story-Rich']],
            ['Resident Evil Village', 'First-person survival horror sequel', 'https://store.steampowered.com/app/1196590/', null, null, 'Resident Evil Village', ['Horror', 'Survival', 'FPS', 'Story-Rich']],
            
            // SILENT HILL FRANCHISE
            ['Silent Hill 2', 'Psychological horror masterpiece', null, null, 'https://www.gog.com/game/silent_hill_2', 'Silent Hill 2', ['Horror', 'Psychological Horror', 'Story-Rich', 'Atmospheric']],
            ['Silent Hill 3', 'Continuation of the Silent Hill saga', null, null, 'https://www.gog.com/game/silent_hill_3', 'Silent Hill 3', ['Horror', 'Psychological Horror', 'Story-Rich']],
            
            // CALL OF DUTY FRANCHISE (Popular titles)
            ['Call of Duty 4: Modern Warfare', 'Revolutionary FPS campaign', 'https://store.steampowered.com/app/7940/', null, null, 'Call of Duty 4: Modern Warfare', ['FPS', 'Military', 'Story-Rich', 'Action']],
            ['Call of Duty: Black Ops', 'Cold War FPS with Zombies mode', 'https://store.steampowered.com/app/42700/', null, null, 'Call of Duty: Black Ops', ['FPS', 'Military', 'Zombies', 'Action', 'Multiplayer']],
            ['Call of Duty: Black Ops II', 'Near-future FPS with branching story', 'https://store.steampowered.com/app/202970/', null, null, 'Call of Duty: Black Ops II', ['FPS', 'Military', 'Zombies', 'Action', 'Multiplayer']],
            ['Call of Duty: Black Ops III', 'Futuristic FPS with advanced movement', 'https://store.steampowered.com/app/311210/', null, null, 'Call of Duty: Black Ops III', ['FPS', 'Military', 'Zombies', 'Action', 'Multiplayer']],
            
            // SUPER MARIO (Randomizer games)
            ['Super Mario 64', 'Classic 3D platformer with popular randomizer', null, null, null, 'Super Mario 64', ['Platformer', 'Adventure', 'Randomizer', 'Speedrun']],
            ['Super Mario World', 'SNES classic with extensive randomizer', null, null, null, 'Super Mario World', ['Platformer', 'Adventure', 'Randomizer', 'Speedrun']],
            ['Super Mario Sunshine', 'GameCube adventure with randomizer', null, null, null, 'Super Mario Sunshine', ['Platformer', 'Adventure', 'Randomizer']],
            ['Super Mario Galaxy', 'Gravity-defying platformer', null, null, null, 'Super Mario Galaxy', ['Platformer', 'Adventure']],
            
            // RPGs WITH RANDOMIZERS
            ['Secret of Mana', 'Action RPG with randomizer support', 'https://store.steampowered.com/app/637670/', null, null, 'Secret of Mana', ['Action RPG', 'Fantasy', 'Multiplayer', 'Randomizer']],
            ['Chrono Trigger', 'Timeless JRPG with randomizer', 'https://store.steampowered.com/app/613830/', null, null, 'Chrono Trigger', ['JRPG', 'Turn-Based', 'Story-Rich', 'Randomizer', 'Time Travel']],
            ['Final Fantasy VI', 'Classic JRPG with randomizer', 'https://store.steampowered.com/app/1173820/', null, null, 'Final Fantasy VI', ['JRPG', 'Turn-Based', 'Story-Rich', 'Randomizer', 'Fantasy']],
            ['EarthBound', 'Quirky RPG with randomizer community', null, null, null, 'EarthBound', ['JRPG', 'Turn-Based', 'Story-Rich', 'Randomizer', 'Comedy']],
            ['The Legend of Zelda: A Link to the Past', 'SNES classic with massive randomizer scene', null, null, null, 'The Legend of Zelda: A Link to the Past', ['Action', 'Adventure', 'Randomizer', 'Speedrun']],
            ['The Legend of Zelda: Ocarina of Time', 'N64 adventure with active randomizer', null, null, null, 'The Legend of Zelda: Ocarina of Time', ['Action', 'Adventure', 'Randomizer', 'Speedrun']],
            ['Metroid Prime', 'First-person adventure with randomizer', null, null, null, 'Metroid Prime', ['Metroidvania', 'FPS', 'Sci-Fi', 'Randomizer']],
            ['Super Metroid', 'Classic Metroidvania with randomizer', null, null, null, 'Super Metroid', ['Metroidvania', 'Platformer', 'Sci-Fi', 'Randomizer', 'Speedrun']],
            ['Castlevania: Symphony of the Night', 'Gothic Metroidvania', null, null, null, 'Castlevania: Symphony of the Night', ['Metroidvania', 'Action', 'Gothic', 'Randomizer']],
            
            // BOOMER SHOOTERS
            ['DOOM', 'Original demon-slaying FPS', 'https://store.steampowered.com/app/2280/', 'https://store.epicgames.com/en-US/p/doom-1993', 'https://www.gog.com/game/doom_the_ultimate_doom', 'DOOM', ['FPS', 'Boomer Shooter', 'Action', 'Gore', 'Classic']],
            ['DOOM II', 'Hell on Earth sequel', 'https://store.steampowered.com/app/2300/', 'https://store.epicgames.com/en-US/p/doom-ii', 'https://www.gog.com/game/doom_ii_final_doom', 'DOOM II', ['FPS', 'Boomer Shooter', 'Action', 'Gore', 'Classic']],
            ['DOOM (2016)', 'Modern revival of demon slaying', 'https://store.steampowered.com/app/379720/', null, null, 'DOOM (2016)', ['FPS', 'Boomer Shooter', 'Action', 'Gore', 'Fast-Paced']],
            ['DOOM Eternal', 'Ultimate demon slaying experience', 'https://store.steampowered.com/app/782330/', 'https://store.epicgames.com/en-US/p/doom-eternal', null, 'DOOM Eternal', ['FPS', 'Boomer Shooter', 'Action', 'Gore', 'Fast-Paced']],
            
            ['Quake', 'Revolutionary 3D shooter', 'https://store.steampowered.com/app/2310/', 'https://store.epicgames.com/en-US/p/quake', 'https://www.gog.com/game/quake_the_offering', 'Quake', ['FPS', 'Boomer Shooter', 'Action', 'Classic', 'Arena Shooter']],
            ['Quake II', 'Sci-fi shooter sequel', 'https://store.steampowered.com/app/2320/', null, 'https://www.gog.com/game/quake_ii_quad_damage', 'Quake II', ['FPS', 'Boomer Shooter', 'Sci-Fi', 'Classic']],
            ['Quake III Arena', 'Legendary arena shooter', 'https://store.steampowered.com/app/2200/', null, 'https://www.gog.com/game/quake_iii_gold', 'Quake III Arena', ['FPS', 'Boomer Shooter', 'Arena Shooter', 'Multiplayer', 'Competitive']],
            ['Quake Champions', 'Modern arena shooter', 'https://store.steampowered.com/app/611500/', null, null, 'Quake Champions', ['FPS', 'Arena Shooter', 'Competitive', 'Multiplayer']],
            
            ['Duke Nukem 3D', 'Over-the-top boomer shooter', 'https://store.steampowered.com/app/225140/', null, 'https://www.gog.com/game/duke_nukem_3d_atomic_edition', 'Duke Nukem 3D', ['FPS', 'Boomer Shooter', 'Action', 'Classic', 'Comedy']],
            ['Blood', 'Dark humor horror shooter', 'https://store.steampowered.com/app/1010750/', null, 'https://www.gog.com/game/blood_fresh_supply', 'Blood: Fresh Supply', ['FPS', 'Boomer Shooter', 'Horror', 'Gore', 'Classic']],
            ['Shadow Warrior', 'Fast-paced katana-and-gun shooter', 'https://store.steampowered.com/app/238070/', 'https://store.epicgames.com/en-US/p/shadow-warrior', 'https://www.gog.com/game/shadow_warrior_complete', 'Shadow Warrior', ['FPS', 'Boomer Shooter', 'Action', 'Sword Fighting']],
            ['Serious Sam', 'Horde-based boomer shooter', 'https://store.steampowered.com/app/41050/', null, 'https://www.gog.com/game/serious_sam_the_first_encounter', 'Serious Sam', ['FPS', 'Boomer Shooter', 'Action', 'Horde', 'Comedy']],
            ['Wolfenstein 3D', 'Grandfather of FPS games', 'https://store.steampowered.com/app/2270/', null, 'https://www.gog.com/game/wolfenstein_3d', 'Wolfenstein 3D', ['FPS', 'Boomer Shooter', 'Classic', 'WW2']],
            ['Wolfenstein: The New Order', 'Modern alt-history shooter', 'https://store.steampowered.com/app/201810/', null, null, 'Wolfenstein: The New Order', ['FPS', 'Action', 'Story-Rich', 'Alternate History']],
            
            ['Dusk', 'Modern retro shooter', 'https://store.steampowered.com/app/519860/', null, 'https://www.gog.com/game/dusk', 'DUSK', ['FPS', 'Boomer Shooter', 'Horror', 'Retro', 'Indie']],
            ['ULTRAKILL', 'Style-focused boomer shooter', 'https://store.steampowered.com/app/1229490/', null, null, 'ULTRAKILL', ['FPS', 'Boomer Shooter', 'Action', 'Fast-Paced', 'Stylish']],
            ['Amid Evil', 'Fantasy retro FPS', 'https://store.steampowered.com/app/673130/', 'https://store.epicgames.com/en-US/p/amid-evil', 'https://www.gog.com/game/amid_evil', 'Amid Evil', ['FPS', 'Boomer Shooter', 'Fantasy', 'Retro']],
            ['Ion Fury', 'Build Engine throwback', 'https://store.steampowered.com/app/562860/', null, 'https://www.gog.com/game/ion_fury', 'Ion Fury', ['FPS', 'Boomer Shooter', 'Cyberpunk', 'Retro']],
            ['Prodeus', 'Modern classic shooter hybrid', 'https://store.steampowered.com/app/964800/', null, 'https://www.gog.com/game/prodeus', 'Prodeus', ['FPS', 'Boomer Shooter', 'Action', 'Gore']],
        ];

        foreach ($games as [$name, $desc, $steam, $epic, $gog, $twitch, $categories]) {
            // Create categories first
            foreach ($categories as $categoryName) {
                $slug = strtolower(str_replace([' ', ':', '&'], ['-', '', 'and'], $categoryName));
                $this->addSql(
                    "INSERT IGNORE INTO categories (name, slug, created_at) VALUES (?, ?, NOW())",
                    [$categoryName, $slug]
                );
            }
        }
        
        // Now insert games with links
        foreach ($games as [$name, $desc, $steam, $epic, $gog, $twitch, $categories]) {
            $this->addSql(
                "INSERT INTO games (name, description, steam_link, epic_link, gog_link, twitch_category, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, NOW())",
                [$name, $desc, $steam, $epic, $gog, $twitch]
            );
            
            // Link to categories
            foreach ($categories as $categoryName) {
                $this->addSql(
                    "INSERT INTO game_categories (game_id, category_id, created_at) 
                     SELECT g.id, c.id, NOW() 
                     FROM games g, categories c 
                     WHERE g.name = ? AND c.name = ?
                     AND NOT EXISTS (
                         SELECT 1 FROM game_categories gc 
                         WHERE gc.game_id = g.id AND gc.category_id = c.id
                     )",
                    [$name, $categoryName]
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql("-- Rollback not implemented for safety");
    }
}

