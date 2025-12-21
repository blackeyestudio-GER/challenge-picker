<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221214930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add categories table, link games to categories, and seed default game categories (FPS, Soulslikes, 3rd Person Shooter)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, slug VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_3AF346685E237E06 (name), UNIQUE INDEX UNIQ_3AF34668989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        
        // Seed default categories
        $now = date('Y-m-d H:i:s');
        $this->addSql("INSERT INTO categories (name, description, slug, created_at) VALUES 
            ('FPS', 'First-Person Shooter games - Experience combat from the player''s perspective', 'fps', '$now'),
            ('Soulslikes', 'Challenging action RPGs inspired by Dark Souls - Prepare to die', 'soulslikes', '$now'),
            ('3rd Person Shooter', 'Third-Person Shooter games - Action from an over-the-shoulder view', '3rd-person-shooter', '$now')
        ");
        
        $this->addSql('ALTER TABLE games ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE games ADD CONSTRAINT FK_FF232B3112469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('CREATE INDEX IDX_FF232B3112469DE2 ON games (category_id)');
        $this->addSql('DROP INDEX UNIQ_1483A5E9D17F50A6 ON users');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE games DROP FOREIGN KEY FK_FF232B3112469DE2');
        $this->addSql('DROP INDEX IDX_FF232B3112469DE2 ON games');
        $this->addSql('ALTER TABLE games DROP category_id');
        $this->addSql('DROP TABLE categories');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9D17F50A6 ON users (uuid)');
    }
}
