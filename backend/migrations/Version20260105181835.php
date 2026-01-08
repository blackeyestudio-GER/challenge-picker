<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260105181835 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Add email verification fields
        $this->addSql('ALTER TABLE users ADD email_verified TINYINT DEFAULT 0 NOT NULL, ADD email_verification_token VARCHAR(100) DEFAULT NULL, ADD email_verification_token_expires_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9C4995C67 ON users (email_verification_token)');
    }

    public function down(Schema $schema): void
    {
        // Remove email verification fields
        $this->addSql('DROP INDEX UNIQ_1483A5E9C4995C67 ON users');
        $this->addSql('ALTER TABLE users DROP email_verified, DROP email_verification_token, DROP email_verification_token_expires_at');
    }
}
