<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260105172205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Add password reset fields to users table
        $this->addSql('ALTER TABLE users ADD password_reset_token VARCHAR(100) DEFAULT NULL, ADD password_reset_token_expires_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E96B7BA4B6 ON users (password_reset_token)');
        
        // Note: Other changes (design_sets, playthroughs, rules) are handled in separate migrations
        // to avoid foreign key constraint conflicts
    }

    public function down(Schema $schema): void
    {
        // Remove password reset fields
        $this->addSql('DROP INDEX UNIQ_1483A5E96B7BA4B6 ON users');
        $this->addSql('ALTER TABLE users DROP password_reset_token, DROP password_reset_token_expires_at');
    }
}
