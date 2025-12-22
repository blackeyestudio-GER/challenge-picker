<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Repopulate game_categories with Twitch game associations
 */
final class Version20251223001800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Repopulate game_categories table with game-category associations';
    }

    public function up(Schema $schema): void
    {
        // Game-category mappings from the original migration
        $games = [
            ['League of Legends', ['MOBA', 'Strategy', 'Competitive', 'Esports', 'Multiplayer']],
            ['Dota 2', ['MOBA', 'Strategy', 'Competitive', 'Esports', 'Multiplayer']],
            ['Fortnite', ['Battle Royale', 'Shooter', 'Building', 'Competitive', 'Multiplayer']],
            ['VALORANT', ['FPS', 'Tactical', 'Competitive', 'Esports', 'Multiplayer']],
            ['Counter-Strike 2', ['FPS', 'Tactical', 'Competitive', 'Esports', 'Multiplayer']],
            ['Apex Legends', ['Battle Royale', 'FPS', 'Competitive', 'Multiplayer']],
            ['Call of Duty: Warzone', ['Battle Royale', 'FPS', 'Military', 'Multiplayer']],
            ['Call of Duty: Modern Warfare III', ['FPS', 'Military', 'Shooter', 'Multiplayer']],
            ['Overwatch 2', ['FPS', 'Hero Shooter', 'Competitive', 'Esports', 'Multiplayer']],
            ['Minecraft', ['Sandbox', 'Survival', 'Building', 'Creative', 'Multiplayer']],
            ['Terraria', ['Sandbox', 'Survival', 'Adventure', 'Building', 'Multiplayer']],
            ['Roblox', ['Sandbox', 'Social', 'Creative', 'Multiplayer']],
            ['World of Warcraft', ['MMORPG', 'Fantasy', 'Adventure', 'Multiplayer']],
            ['Final Fantasy XIV', ['MMORPG', 'Fantasy', 'Story-Rich', 'Multiplayer']],
            ['Lost Ark', ['MMORPG', 'Action RPG', 'Fantasy', 'Multiplayer']],
            ['Path of Exile', ['Action RPG', 'Dungeon Crawler', 'Dark Fantasy', 'Multiplayer']],
            ['EA Sports FC 24', ['Sports', 'Soccer', 'Competitive', 'Multiplayer']],
            ['NBA 2K24', ['Sports', 'Basketball', 'Competitive', 'Multiplayer']],
            ['Rocket League', ['Sports', 'Racing', 'Competitive', 'Multiplayer']],
            ['Gran Turismo 7', ['Racing', 'Simulation', 'Competitive']],
            ['Street Fighter 6', ['Fighting', 'Competitive', 'Esports', 'Multiplayer']],
            ['Tekken 8', ['Fighting', 'Competitive', 'Esports', 'Multiplayer']],
            ['Mortal Kombat 1', ['Fighting', 'Gore', 'Competitive', 'Multiplayer']],
            ['Hearthstone', ['Card Game', 'Strategy', 'Competitive', 'Multiplayer']],
            ['Teamfight Tactics', ['Auto Battler', 'Strategy', 'Competitive', 'Multiplayer']],
            ['Marvel Snap', ['Card Game', 'Strategy', 'Competitive', 'Multiplayer']],
            ['Dead by Daylight', ['Horror', 'Survival', 'Asymmetric', 'Multiplayer']],
            ['Phasmophobia', ['Horror', 'Survival', 'Co-op', 'Multiplayer']],
            ['Lethal Company', ['Horror', 'Survival', 'Co-op', 'Comedy', 'Multiplayer']],
            ['Rust', ['Survival', 'Crafting', 'Open World', 'Multiplayer']],
            ['ARK: Survival Ascended', ['Survival', 'Dinosaurs', 'Crafting', 'Open World', 'Multiplayer']],
            ['Baldur\'s Gate 3', ['RPG', 'Story-Rich', 'Turn-Based', 'Fantasy']],
            ['Cyberpunk 2077', ['RPG', 'Open World', 'Story-Rich', 'Sci-Fi']],
            ['The Witcher 3', ['RPG', 'Open World', 'Story-Rich', 'Fantasy']],
            ['Elden Ring', ['Souls-like', 'RPG', 'Dark Fantasy', 'Open World']],
            ['Starfield', ['RPG', 'Sci-Fi', 'Space', 'Open World']],
            ['Grand Theft Auto V', ['Open World', 'Action', 'Crime', 'Multiplayer']],
            ['Red Dead Redemption 2', ['Open World', 'Western', 'Story-Rich']],
            ['The Sims 4', ['Simulation', 'Life Sim', 'Creative']],
            ['Cities: Skylines', ['Simulation', 'City Builder', 'Strategy']],
            ['Euro Truck Simulator 2', ['Simulation', 'Driving', 'Relaxing']],
            ['Among Us', ['Social Deduction', 'Party Game', 'Multiplayer']],
            ['Fall Guys', ['Party Game', 'Platformer', 'Competitive', 'Multiplayer']],
            ['Jackbox Party Pack', ['Party Game', 'Trivia', 'Multiplayer']],
            ['Resident Evil 4', ['Horror', 'Survival', 'Action', 'Story-Rich']],
            ['Spider-Man', ['Action', 'Adventure', 'Superhero', 'Story-Rich']],
            ['God of War', ['Action', 'Adventure', 'Story-Rich', 'Mythology']],
            ['Hogwarts Legacy', ['RPG', 'Open World', 'Magic', 'Story-Rich']],
            ['Stardew Valley', ['Farming', 'Simulation', 'Relaxing', 'Indie']],
            ['Hollow Knight', ['Metroidvania', 'Platformer', 'Indie']],
            ['Hades', ['Roguelike', 'Action', 'Indie']],
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
        $this->addSql('DELETE FROM game_categories');
    }
}

