<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221215412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add additional game categories (Horror, Zombies, Aliens, Monsters) and create default games for each category';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');
        
        // Add new categories: Horror, Zombies, Aliens, Monsters
        $this->addSql("INSERT INTO categories (name, description, slug, created_at) VALUES 
            ('Horror', 'Psychological horror and survival games - Face your fears', 'horror', '$now'),
            ('Zombies', 'Zombie apocalypse and survival games - Survive the undead', 'zombies', '$now'),
            ('Aliens', 'Sci-fi games featuring extraterrestrial threats - Fight for humanity', 'aliens', '$now'),
            ('Monsters', 'Games featuring mythical beasts and creatures - Hunt or be hunted', 'monsters', '$now')
        ");
        
        // Create default games for each category (using category names as game names)
        // Get category IDs first, then create games
        $this->addSql("
            INSERT INTO games (name, description, image, category_id, created_at)
            SELECT 
                c.name as name,
                CONCAT('Explore challenging ', c.name, ' games with custom rule sets and live configuration') as description,
                NULL as image,
                c.id as category_id,
                '$now' as created_at
            FROM categories c
            WHERE c.slug IN ('fps', 'soulslikes', '3rd-person-shooter', 'horror', 'zombies', 'aliens', 'monsters')
        ");
    }

    public function down(Schema $schema): void
    {
        // Remove the default games
        $this->addSql("DELETE FROM games WHERE name IN ('FPS', 'Soulslikes', '3rd Person Shooter', 'Horror', 'Zombies', 'Aliens', 'Monsters')");
        
        // Remove the new categories
        $this->addSql("DELETE FROM categories WHERE slug IN ('horror', 'zombies', 'aliens', 'monsters')");
    }
}
