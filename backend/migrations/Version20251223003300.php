<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create representative games for all categories and add rule_categories table
 */
final class Version20251223003300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create representative games for all categories and add rule-category scoring system';
    }

    public function up(Schema $schema): void
    {
        // Create rule_categories table for hybrid recommendation system
        $this->addSql('CREATE TABLE rule_categories (
            id INT AUTO_INCREMENT NOT NULL,
            rule_id INT NOT NULL,
            category_id INT NOT NULL,
            manual_relevance_score SMALLINT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            INDEX IDX_RULE_CAT_RULE (rule_id),
            INDEX IDX_RULE_CAT_CATEGORY (category_id, manual_relevance_score),
            UNIQUE KEY unique_rule_category (rule_id, category_id),
            CONSTRAINT FK_RULE_CAT_RULE FOREIGN KEY (rule_id) REFERENCES rules (id) ON DELETE CASCADE,
            CONSTRAINT FK_RULE_CAT_CATEGORY FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE,
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');

        // Get all categories that don't have a representative game
        $categoriesWithoutGames = $this->connection->fetchAllAssociative(
            "SELECT c.id, c.name 
             FROM categories c 
             LEFT JOIN games g ON g.name = c.name AND g.is_category_representative = 1 
             WHERE g.id IS NULL 
             ORDER BY c.name"
        );

        // Create a representative game for each category
        foreach ($categoriesWithoutGames as $category) {
            $categoryId = $category['id'];
            $categoryName = $category['name'];
            
            // Insert the game
            $this->addSql(
                "INSERT INTO games (name, description, is_category_representative, created_at) 
                 VALUES (?, ?, 1, NOW())",
                [$categoryName, "Representative game for {$categoryName} category"]
            );
            
            // Link the game to its category
            $this->addSql(
                "INSERT INTO game_categories (game_id, category_id, created_at)
                 SELECT g.id, ?, NOW()
                 FROM games g
                 WHERE g.name = ? AND g.is_category_representative = 1
                 LIMIT 1",
                [$categoryId, $categoryName]
            );
        }
    }

    public function down(Schema $schema): void
    {
        // Drop rule_categories table
        $this->addSql('ALTER TABLE rule_categories DROP FOREIGN KEY FK_RULE_CAT_RULE');
        $this->addSql('ALTER TABLE rule_categories DROP FOREIGN KEY FK_RULE_CAT_CATEGORY');
        $this->addSql('DROP TABLE rule_categories');
        
        // Don't remove representative games in down migration for safety
        $this->addSql('-- Representative games not removed for safety');
    }
}

