<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251230205601 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rule_icons (id INT AUTO_INCREMENT NOT NULL, identifier VARCHAR(100) NOT NULL, category VARCHAR(50) NOT NULL, display_name VARCHAR(100) NOT NULL, svg_content LONGTEXT NOT NULL, tags JSON DEFAULT NULL, color VARCHAR(7) DEFAULT NULL, license VARCHAR(50) DEFAULT NULL, source VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_C45870BE772E836A (identifier), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE rules ADD icon_identifier VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE rule_icons');
        $this->addSql('ALTER TABLE rules DROP icon_identifier');
    }
}
