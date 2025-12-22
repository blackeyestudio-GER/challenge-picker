<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251222234449 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Update Game entity to use ManyToMany relationship with Category
        // Check and drop FK constraint if it exists
        $this->addSql('SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = "games" AND CONSTRAINT_NAME = "FK_FF232B3112469DE2")');
        $this->addSql('SET @sql = IF(@fk_exists > 0, "ALTER TABLE games DROP FOREIGN KEY `FK_FF232B3112469DE2`", "SELECT 1")');
        $this->addSql('PREPARE stmt FROM @sql');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');
        
        // Check and drop index if it exists
        $this->addSql('SET @idx_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = "games" AND INDEX_NAME = "IDX_FF232B3112469DE2")');
        $this->addSql('SET @sql = IF(@idx_exists > 0, "DROP INDEX IDX_FF232B3112469DE2 ON games", "SELECT 1")');
        $this->addSql('PREPARE stmt FROM @sql');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');
        
        // Check and drop category_id column if it exists
        $this->addSql('SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = "games" AND COLUMN_NAME = "category_id")');
        $this->addSql('SET @sql = IF(@col_exists > 0, "ALTER TABLE games DROP category_id", "SELECT 1")');
        $this->addSql('PREPARE stmt FROM @sql');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');
        
        // Update game_categories table
        $this->addSql('SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = "game_categories" AND COLUMN_NAME = "created_at")');
        $this->addSql('SET @sql = IF(@col_exists > 0, "ALTER TABLE game_categories DROP created_at", "SELECT 1")');
        $this->addSql('PREPARE stmt FROM @sql');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_categories ADD created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE game_categories RENAME INDEX idx_d42f818512469de2 TO IDX_5E4D1F0012469DE2');
        $this->addSql('ALTER TABLE game_categories RENAME INDEX idx_d42f8185e48fd905 TO IDX_5E4D1F00E48FD905');
        $this->addSql('ALTER TABLE games ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE games ADD CONSTRAINT `FK_FF232B3112469DE2` FOREIGN KEY (category_id) REFERENCES categories (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_FF232B3112469DE2 ON games (category_id)');
        // Skip rule_categories index changes
        // $this->addSql('CREATE INDEX IDX_RULE_CAT_CATEGORY ON rule_categories (category_id, manual_relevance_score)');
        // $this->addSql('ALTER TABLE rule_categories RENAME INDEX idx_142cab3744e0351 TO IDX_RULE_CAT_RULE');
    }
}
