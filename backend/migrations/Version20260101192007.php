<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260101192007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Add pause tracking fields
        $this->addSql('ALTER TABLE playthroughs ADD paused_at DATETIME DEFAULT NULL, ADD total_paused_duration INT DEFAULT NULL');
        
        // Make configuration NOT NULL (always present)
        $this->addSql("UPDATE playthroughs SET configuration = JSON_OBJECT('version', '1.0') WHERE configuration IS NULL");
        $this->addSql('ALTER TABLE playthroughs CHANGE configuration configuration JSON NOT NULL');
        
        // Make maxConcurrentRules NOT NULL with default
        $this->addSql('ALTER TABLE playthroughs CHANGE max_concurrent_rules max_concurrent_rules INT NOT NULL DEFAULT 3');
        
        // Make status NOT NULL with default
        $this->addSql("ALTER TABLE playthroughs CHANGE status status VARCHAR(20) NOT NULL DEFAULT 'setup'");
        
        // Make createdAt NOT NULL
        $this->addSql("UPDATE playthroughs SET created_at = NOW() WHERE created_at IS NULL");
        $this->addSql('ALTER TABLE playthroughs CHANGE created_at created_at DATETIME NOT NULL');
        
        // Make uuid NOT NULL (should already be set, but ensure it)
        // Keep as BINARY(16) for UUID v7 (UuidType expects binary, not CHAR)
        $this->addSql('ALTER TABLE playthroughs CHANGE uuid uuid BINARY(16) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Revert NOT NULL constraints
        $this->addSql('ALTER TABLE playthroughs CHANGE uuid uuid BINARY(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE playthroughs CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql("ALTER TABLE playthroughs CHANGE status status VARCHAR(20) DEFAULT NULL");
        $this->addSql('ALTER TABLE playthroughs CHANGE max_concurrent_rules max_concurrent_rules INT DEFAULT NULL');
        $this->addSql('ALTER TABLE playthroughs CHANGE configuration configuration JSON DEFAULT NULL');
        
        // Remove pause tracking fields
        $this->addSql('ALTER TABLE playthroughs DROP paused_at, DROP total_paused_duration');
    }
}
