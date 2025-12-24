<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251224122321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add representative_game_id to categories table with unique constraint';
    }

    public function up(Schema $schema): void
    {
        // Add representative_game_id column to categories table
        $this->addSql('ALTER TABLE categories ADD representative_game_id INT DEFAULT NULL');
        
        // Add unique constraint (each category can only have ONE representative game)
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT UNIQ_3AF34668A1C01850 UNIQUE (representative_game_id)');
        
        // Add foreign key constraint
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF34668A1C01850 FOREIGN KEY (representative_game_id) REFERENCES games (id)');
        
        // Populate representative_game_id for each category
        // Find the game that is marked as representative for each category
        $this->addSql('
            UPDATE categories c
            INNER JOIN game_categories gc ON c.id = gc.category_id
            INNER JOIN games g ON g.id = gc.game_id
            SET c.representative_game_id = g.id
            WHERE g.is_category_representative = 1
        ');
    }

    public function down(Schema $schema): void
    {
        // Remove foreign key constraint
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668A1C01850');
        
        // Remove unique constraint
        $this->addSql('ALTER TABLE categories DROP INDEX UNIQ_3AF34668A1C01850');
        
        // Remove column
        $this->addSql('ALTER TABLE categories DROP representative_game_id');
    }
}
