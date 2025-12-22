<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Associate remaining games with appropriate categories
 */
final class Version20251223002100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add category associations for remaining 63 games';
    }

    public function up(Schema $schema): void
    {
        // Game-category mappings
        $games = [
            // Resident Evil Series
            ['Resident Evil (1996)', ['Horror', 'Survival', 'Action', 'Randomizer']],
            ['Resident Evil (2002)', ['Horror', 'Survival', 'Action', 'Remake']],
            ['Resident Evil 0 (2002)', ['Horror', 'Survival', 'Action']],
            ['Resident Evil 2 (1998)', ['Horror', 'Survival', 'Action', 'Randomizer']],
            ['Resident Evil 2 (2019)', ['Horror', 'Survival', 'Action', 'Remake']],
            ['Resident Evil 3: Nemesis (1999)', ['Horror', 'Survival', 'Action']],
            ['Resident Evil 3 (2020)', ['Horror', 'Survival', 'Action', 'Remake']],
            ['Resident Evil 4 (2005)', ['Horror', 'Survival', 'Action']],
            ['Resident Evil 4 (2023)', ['Horror', 'Survival', 'Action', 'Remake']],
            ['Resident Evil 7', ['Horror', 'Survival', 'FPS']],
            ['Resident Evil Code: Veronica (2000)', ['Horror', 'Survival', 'Action']],
            ['Resident Evil Revelations (2012)', ['Horror', 'Survival', 'Action']],
            ['Resident Evil Revelations 2 (2015)', ['Horror', 'Survival', 'Action', 'Co-op']],
            ['Resident Evil Village (2021)', ['Horror', 'Survival', 'Action', 'FPS']],
            
            // Call of Duty Series
            ['Call of Duty 4: Modern Warfare', ['FPS', 'Military', 'Shooter']],
            ['Call of Duty: Modern Warfare', ['FPS', 'Military', 'Shooter']],
            ['Call of Duty: Black Ops', ['FPS', 'Military', 'Shooter']],
            ['Call of Duty: Black Ops II', ['FPS', 'Military', 'Shooter']],
            ['Call of Duty: Black Ops III', ['FPS', 'Military', 'Shooter']],
            
            // Silent Hill Series
            ['Silent Hill 2', ['Horror', 'Survival', 'Psychological']],
            ['Silent Hill 3', ['Horror', 'Survival', 'Psychological']],
            
            // Super Mario Series
            ['Super Mario 64', ['Platformer', 'Adventure', 'Randomizer', 'Speedrun']],
            ['Super Mario World', ['Platformer', 'Adventure', 'Randomizer']],
            ['Super Mario Sunshine', ['Platformer', 'Adventure', 'Speedrun']],
            ['Super Mario Galaxy', ['Platformer', 'Adventure']],
            
            // Souls-like
            ['Dark Souls III', ['Souls-like', 'RPG', 'Dark Fantasy']],
            ['Bloodborne', ['Souls-like', 'RPG', 'Dark Fantasy', 'Horror']],
            
            // Classic RPGs
            ['Chrono Trigger', ['RPG', 'Turn-Based', 'Randomizer']],
            ['Final Fantasy VI', ['RPG', 'Turn-Based', 'Randomizer']],
            ['EarthBound', ['RPG', 'Turn-Based', 'Randomizer']],
            ['Secret of Mana', ['Action RPG', 'Fantasy', 'Randomizer']],
            
            // Metroidvania
            ['Castlevania: Symphony of the Night', ['Metroidvania', 'Action', 'Randomizer']],
            ['Super Metroid', ['Metroidvania', 'Sci-Fi', 'Randomizer']],
            ['Metroid Prime', ['FPS', 'Metroidvania', 'Sci-Fi']],
            
            // Zelda Series
            ['The Legend of Zelda: A Link to the Past', ['Action', 'Adventure', 'Randomizer']],
            ['The Legend of Zelda: Ocarina of Time', ['Action', 'Adventure', 'Randomizer', 'Speedrun']],
            
            // DOOM Series (Boomer Shooters)
            ['DOOM', ['FPS', 'Boomer Shooter', 'Action', 'Randomizer']],
            ['DOOM II', ['FPS', 'Boomer Shooter', 'Action', 'Randomizer']],
            ['DOOM (2016)', ['FPS', 'Boomer Shooter', 'Action']],
            ['DOOM Eternal', ['FPS', 'Boomer Shooter', 'Action']],
            
            // Quake Series
            ['Quake', ['FPS', 'Boomer Shooter', 'Action', 'Speedrun']],
            ['Quake II', ['FPS', 'Boomer Shooter', 'Sci-Fi']],
            ['Quake III Arena', ['FPS', 'Arena Shooter', 'Competitive', 'Multiplayer']],
            ['Quake Champions', ['FPS', 'Arena Shooter', 'Competitive', 'Multiplayer']],
            
            // Other Boomer Shooters
            ['Duke Nukem 3D', ['FPS', 'Boomer Shooter', 'Action']],
            ['Blood', ['FPS', 'Boomer Shooter', 'Horror']],
            ['Shadow Warrior', ['FPS', 'Boomer Shooter', 'Action']],
            ['Serious Sam', ['FPS', 'Boomer Shooter', 'Action']],
            ['Wolfenstein 3D', ['FPS', 'Boomer Shooter', 'Action']],
            ['Wolfenstein: The New Order', ['FPS', 'Shooter', 'Story-Rich']],
            
            // Modern Boomer Shooters
            ['Dusk', ['FPS', 'Boomer Shooter', 'Horror', 'Indie']],
            ['Amid Evil', ['FPS', 'Boomer Shooter', 'Fantasy', 'Indie']],
            ['Ion Fury', ['FPS', 'Boomer Shooter', 'Action', 'Indie']],
            ['ULTRAKILL', ['FPS', 'Boomer Shooter', 'Action', 'Indie']],
            ['Prodeus', ['FPS', 'Boomer Shooter', 'Action', 'Indie']],
            
            // Other FPS/Action
            ['Halo Infinite', ['FPS', 'Sci-Fi', 'Shooter', 'Multiplayer']],
            ['Gears of War', ['Third Person Shooter', 'Action', 'Sci-Fi']],
            ['Left 4 Dead 2', ['FPS', 'Co-op', 'Zombies', 'Survival']],
            ['Dead Space', ['Horror', 'Survival', 'Sci-Fi', 'Third Person Shooter']],
            ['The Last of Us', ['Horror', 'Survival', 'Story-Rich', 'Third Person Shooter']],
            ['Alien: Isolation', ['Horror', 'Survival', 'Stealth', 'Sci-Fi']],
            ['Dying Light 2', ['FPS', 'Parkour', 'Zombies', 'Open World']],
            
            // Action RPG
            ['Monster Hunter: World', ['Action RPG', 'Co-op', 'Multiplayer']],
        ];

        // Link games to categories
        foreach ($games as [$gameName, $categories]) {
            foreach ($categories as $categoryName) {
                $this->addSql(
                    "INSERT IGNORE INTO game_categories (game_id, category_id, created_at) 
                     SELECT g.id, c.id, NOW() 
                     FROM games g, categories c 
                     WHERE g.name = ? AND c.name = ?",
                    [$gameName, $categoryName]
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        // Don't remove associations in down migration for safety
        $this->addSql('-- Rollback not implemented for safety');
    }
}

