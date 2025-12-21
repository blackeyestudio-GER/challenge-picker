<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create game_categories table for direct associations, separate from voting
 */
final class Version20251222001000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create game_categories table for direct many-to-many associations, separate from voting system';
    }

    public function up(Schema $schema): void
    {
        // Create game_categories table for direct associations
        $this->addSql('
            CREATE TABLE game_categories (
                id INT AUTO_INCREMENT NOT NULL,
                game_id INT NOT NULL,
                category_id INT NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX IDX_5E4D1F00E48FD905 (game_id),
                INDEX IDX_5E4D1F0012469DE2 (category_id),
                UNIQUE INDEX unique_game_category (game_id, category_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql('
            ALTER TABLE game_categories 
            ADD CONSTRAINT FK_5E4D1F00E48FD905 FOREIGN KEY (game_id) REFERENCES games (id) ON DELETE CASCADE
        ');
        
        $this->addSql('
            ALTER TABLE game_categories 
            ADD CONSTRAINT FK_5E4D1F0012469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE
        ');

        // Migrate existing system votes to direct associations
        $this->addSql('
            INSERT INTO game_categories (game_id, category_id, created_at)
            SELECT DISTINCT gcv.game_id, gcv.category_id, MIN(gcv.created_at)
            FROM game_category_votes gcv
            GROUP BY gcv.game_id, gcv.category_id
        ');

        // Remove system user votes (no longer needed, replaced by direct associations)
        $this->addSql('
            DELETE gcv FROM game_category_votes gcv
            INNER JOIN users u ON gcv.user_uuid = u.uuid
            WHERE u.email = \'system@challenge-picker.local\'
        ');
    }

    public function down(Schema $schema): void
    {
        // Restore system votes before dropping the table
        $this->addSql('SET @system_user = (SELECT uuid FROM users WHERE email = \'system@challenge-picker.local\')');
        
        $this->addSql('
            INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at)
            SELECT gc.game_id, gc.category_id, @system_user, gc.created_at
            FROM game_categories gc
        ');

        // Drop the game_categories table
        $this->addSql('ALTER TABLE game_categories DROP FOREIGN KEY FK_5E4D1F00E48FD905');
        $this->addSql('ALTER TABLE game_categories DROP FOREIGN KEY FK_5E4D1F0012469DE2');
        $this->addSql('DROP TABLE game_categories');
    }
}

