<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create representative games for categories that don't have one
 */
final class Version20251223230914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create representative games for all categories missing them';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');
        
        // Categories without representative games:
        // 1. Aliens (id: 6)
        // 2. FPS (id: 1)
        // 3. Horror (id: 4)
        // 4. Monsters (id: 7)
        // 5. Soulslikes (id: 2)
        // 6. Zombies (id: 5)
        
        // Create representative games for each category
        $categories = [
            ['id' => 6, 'name' => 'Aliens', 'slug' => 'aliens'],
            ['id' => 1, 'name' => 'FPS', 'slug' => 'fps'],
            ['id' => 4, 'name' => 'Horror', 'slug' => 'horror'],
            ['id' => 7, 'name' => 'Monsters', 'slug' => 'monsters'],
            ['id' => 2, 'name' => 'Soulslikes', 'slug' => 'soulslikes'],
            ['id' => 5, 'name' => 'Zombies', 'slug' => 'zombies'],
        ];
        
        foreach ($categories as $category) {
            // Check if a game with this name already exists
            $result = $this->connection->executeQuery(
                "SELECT id FROM games WHERE name = ? LIMIT 1",
                [$category['name']]
            )->fetchOne();
            
            if ($result) {
                // Game exists, update it to be a category representative and link it
                $gameId = $result;
                $this->addSql(
                    "UPDATE games SET is_category_representative = 1 WHERE id = ?",
                    [$gameId]
                );
                
                // Check if the game_categories association exists
                $associationExists = $this->connection->executeQuery(
                    "SELECT COUNT(*) FROM game_categories WHERE game_id = ? AND category_id = ?",
                    [$gameId, $category['id']]
                )->fetchOne();
                
                if (!$associationExists) {
                    $this->addSql(
                        "INSERT INTO game_categories (game_id, category_id) VALUES (?, ?)",
                        [$gameId, $category['id']]
                    );
                }
            } else {
                // Game doesn't exist, create it
                $this->addSql(
                    "INSERT INTO games (name, is_category_representative, is_active, created_at) VALUES (?, 1, 1, ?)",
                    [$category['name'], $now]
                );
                
                // Get the newly created game ID and link it to the category
                $gameId = $this->connection->lastInsertId();
                $this->addSql(
                    "INSERT INTO game_categories (game_id, category_id) VALUES (?, ?)",
                    [$gameId, $category['id']]
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        // We don't want to delete games that might have been manually created
        // So we just remove the is_category_representative flag
        $this->addSql("UPDATE games SET is_category_representative = 0 WHERE name IN ('Aliens', 'FPS', 'Horror', 'Monsters', 'Soulslikes', 'Zombies')");
    }
}
