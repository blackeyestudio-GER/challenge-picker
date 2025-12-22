<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Seed top 50 Twitch games with their categories
 */
final class Version20251222214200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add top 50 Twitch games with appropriate categories';
    }

    public function up(Schema $schema): void
    {
        // First, create the game_categories association table if it doesn't exist
        $this->addSql('CREATE TABLE IF NOT EXISTS game_categories (
            game_id INT NOT NULL,
            category_id INT NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (game_id, category_id),
            INDEX IDX_5E4D1F00E48FD905 (game_id),
            INDEX IDX_5E4D1F0012469DE2 (category_id),
            CONSTRAINT FK_5E4D1F00E48FD905 FOREIGN KEY (game_id) REFERENCES games (id) ON DELETE CASCADE,
            CONSTRAINT FK_5E4D1F0012469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Top 50 Twitch games with their categories
        // Format: [game_name, [categories]]
        $games = [
            // MOBAs & Strategy
            ['League of Legends', ['MOBA', 'Strategy', 'Competitive', 'Esports', 'Multiplayer']],
            ['Dota 2', ['MOBA', 'Strategy', 'Competitive', 'Esports', 'Multiplayer']],
            
            // Battle Royale & Shooters
            ['Fortnite', ['Battle Royale', 'Shooter', 'Building', 'Competitive', 'Multiplayer']],
            ['VALORANT', ['FPS', 'Tactical', 'Competitive', 'Esports', 'Multiplayer']],
            ['Counter-Strike 2', ['FPS', 'Tactical', 'Competitive', 'Esports', 'Multiplayer']],
            ['Apex Legends', ['Battle Royale', 'FPS', 'Competitive', 'Multiplayer']],
            ['Call of Duty: Warzone', ['Battle Royale', 'FPS', 'Military', 'Multiplayer']],
            ['Call of Duty: Modern Warfare III', ['FPS', 'Military', 'Shooter', 'Multiplayer']],
            ['Overwatch 2', ['FPS', 'Hero Shooter', 'Competitive', 'Esports', 'Multiplayer']],
            
            // Minecraft & Sandbox
            ['Minecraft', ['Sandbox', 'Survival', 'Building', 'Creative', 'Multiplayer']],
            ['Terraria', ['Sandbox', 'Survival', 'Adventure', 'Building', 'Multiplayer']],
            ['Roblox', ['Sandbox', 'Social', 'Creative', 'Multiplayer']],
            
            // MMORPGs
            ['World of Warcraft', ['MMORPG', 'Fantasy', 'Adventure', 'Multiplayer']],
            ['Final Fantasy XIV', ['MMORPG', 'Fantasy', 'Story-Rich', 'Multiplayer']],
            ['Lost Ark', ['MMORPG', 'Action RPG', 'Fantasy', 'Multiplayer']],
            ['Path of Exile', ['Action RPG', 'Dungeon Crawler', 'Dark Fantasy', 'Multiplayer']],
            
            // Sports & Racing
            ['EA Sports FC 24', ['Sports', 'Soccer', 'Competitive', 'Multiplayer']],
            ['NBA 2K24', ['Sports', 'Basketball', 'Competitive', 'Multiplayer']],
            ['Rocket League', ['Sports', 'Racing', 'Competitive', 'Multiplayer']],
            ['Gran Turismo 7', ['Racing', 'Simulation', 'Competitive']],
            
            // Fighting Games
            ['Street Fighter 6', ['Fighting', 'Competitive', 'Esports', 'Multiplayer']],
            ['Tekken 8', ['Fighting', 'Competitive', 'Esports', 'Multiplayer']],
            ['Mortal Kombat 1', ['Fighting', 'Gore', 'Competitive', 'Multiplayer']],
            
            // Card & Auto Battlers
            ['Hearthstone', ['Card Game', 'Strategy', 'Competitive', 'Multiplayer']],
            ['Teamfight Tactics', ['Auto Battler', 'Strategy', 'Competitive', 'Multiplayer']],
            ['Marvel Snap', ['Card Game', 'Strategy', 'Competitive', 'Multiplayer']],
            
            // Survival & Horror
            ['Dead by Daylight', ['Horror', 'Survival', 'Asymmetric', 'Multiplayer']],
            ['Phasmophobia', ['Horror', 'Survival', 'Co-op', 'Multiplayer']],
            ['Lethal Company', ['Horror', 'Survival', 'Co-op', 'Comedy', 'Multiplayer']],
            ['Rust', ['Survival', 'Crafting', 'Open World', 'Multiplayer']],
            ['ARK: Survival Ascended', ['Survival', 'Dinosaurs', 'Crafting', 'Open World', 'Multiplayer']],
            
            // Story & RPG
            ['Baldur\'s Gate 3', ['RPG', 'Story-Rich', 'Turn-Based', 'Fantasy']],
            ['Cyberpunk 2077', ['RPG', 'Open World', 'Story-Rich', 'Sci-Fi']],
            ['The Witcher 3', ['RPG', 'Open World', 'Story-Rich', 'Fantasy']],
            ['Elden Ring', ['Souls-like', 'RPG', 'Dark Fantasy', 'Open World']],
            ['Starfield', ['RPG', 'Sci-Fi', 'Space', 'Open World']],
            
            // GTA & Open World
            ['Grand Theft Auto V', ['Open World', 'Action', 'Crime', 'Multiplayer']],
            ['Red Dead Redemption 2', ['Open World', 'Western', 'Story-Rich']],
            
            // Simulation
            ['The Sims 4', ['Simulation', 'Life Sim', 'Creative']],
            ['Cities: Skylines', ['Simulation', 'City Builder', 'Strategy']],
            ['Euro Truck Simulator 2', ['Simulation', 'Driving', 'Relaxing']],
            
            // Party & Social
            ['Among Us', ['Social Deduction', 'Party Game', 'Multiplayer']],
            ['Fall Guys', ['Party Game', 'Platformer', 'Competitive', 'Multiplayer']],
            ['Jackbox Party Pack', ['Party Game', 'Trivia', 'Multiplayer']],
            
            // Action & Adventure
            ['Resident Evil 4', ['Horror', 'Survival', 'Action', 'Story-Rich']],
            ['Spider-Man', ['Action', 'Adventure', 'Superhero', 'Story-Rich']],
            ['God of War', ['Action', 'Adventure', 'Story-Rich', 'Mythology']],
            ['Hogwarts Legacy', ['RPG', 'Open World', 'Magic', 'Story-Rich']],
            
            // Retro & Indie
            ['Stardew Valley', ['Farming', 'Simulation', 'Relaxing', 'Indie']],
            ['Hollow Knight', ['Metroidvania', 'Platformer', 'Indie']],
            ['Hades', ['Roguelike', 'Action', 'Indie']],
        ];

        foreach ($games as [$gameName, $categories]) {
            // For each category, create or link it
            foreach ($categories as $categoryName) {
                // Create slug from category name (lowercase, spaces to hyphens)
                $slug = strtolower(str_replace([' ', ':', '&'], ['-', '', 'and'], $categoryName));
                
                // Insert category if it doesn't exist (ignore if exists)
                $this->addSql(
                    "INSERT IGNORE INTO categories (name, slug, created_at) VALUES (?, ?, NOW())",
                    [$categoryName, $slug]
                );
            }
        }
        
        // Now insert games and link to categories
        foreach ($games as [$gameName, $categories]) {
            // Build category condition for INSERT
            $categoryPlaceholders = implode(',', array_fill(0, count($categories), '?'));
            
            // Insert game and link to all categories in one statement
            $this->addSql(
                "INSERT INTO games (name, description, created_at) VALUES (?, ?, NOW())",
                [$gameName, "Popular Twitch game"]
            );
            
            // Link game to categories using a subquery
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
                    [$gameName, $categoryName]
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        // Optionally remove the games (not implemented for safety)
        $this->addSql("-- Rollback not implemented");
    }
}

