<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Restore game_categories table that was accidentally dropped
 */
final class Version20251223001700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Restore game_categories junction table for game-category associations';
    }

    public function up(Schema $schema): void
    {
        // Recreate the game_categories table
        $this->addSql('CREATE TABLE game_categories (
            game_id INT NOT NULL, 
            category_id INT NOT NULL, 
            created_at DATETIME NOT NULL, 
            INDEX IDX_5E4D1F00E48FD905 (game_id), 
            INDEX IDX_5E4D1F0012469DE2 (category_id), 
            PRIMARY KEY (game_id, category_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        
        $this->addSql('ALTER TABLE game_categories ADD CONSTRAINT FK_5E4D1F00E48FD905 FOREIGN KEY (game_id) REFERENCES games (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_categories ADD CONSTRAINT FK_5E4D1F0012469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE');
        
        // Repopulate with existing game-category associations from votes
        // Get all unique game-category pairs from game_category_votes where there are votes
        $this->addSql('
            INSERT INTO game_categories (game_id, category_id, created_at)
            SELECT DISTINCT game_id, category_id, NOW()
            FROM game_category_votes
            GROUP BY game_id, category_id
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE game_categories DROP FOREIGN KEY FK_5E4D1F00E48FD905');
        $this->addSql('ALTER TABLE game_categories DROP FOREIGN KEY FK_5E4D1F0012469DE2');
        $this->addSql('DROP TABLE game_categories');
    }
}

