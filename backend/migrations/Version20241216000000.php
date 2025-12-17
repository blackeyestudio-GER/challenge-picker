<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial database setup - User authentication
 * 
 * Creates users table with support for:
 * - Email/password authentication
 * - OAuth providers (Twitch, Discord)
 */
final class Version20241216000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial setup: users table with email/password and OAuth support';
    }

    public function up(Schema $schema): void
    {
        // Create users table with OAuth support
        $this->addSql('
            CREATE TABLE users (
                id INT AUTO_INCREMENT NOT NULL,
                email VARCHAR(180) NOT NULL,
                username VARCHAR(50) NOT NULL,
                password VARCHAR(255) DEFAULT NULL,
                oauth_provider VARCHAR(20) DEFAULT NULL,
                oauth_id VARCHAR(255) DEFAULT NULL,
                avatar TEXT DEFAULT NULL,
                roles JSON NOT NULL,
                created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email),
                UNIQUE INDEX UNIQ_1483A5E9F85E0677 (username),
                INDEX IDX_1483A5E9_OAUTH (oauth_provider, oauth_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
    }
}

