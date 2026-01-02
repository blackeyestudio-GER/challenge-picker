<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260102095023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add refresh_token and refresh_token_expires_at fields to users table for 30-day "Remember Me" functionality';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD refresh_token VARCHAR(255) DEFAULT NULL, ADD refresh_token_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP refresh_token, DROP refresh_token_expires_at');
    }
}
