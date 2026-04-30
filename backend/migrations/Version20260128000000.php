<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * The RefreshToken entity was added without a migration; fixture purge touches this table.
 */
final class Version20260128000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create refresh_tokens table for App\Entity\RefreshToken';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE refresh_tokens (
            id INT AUTO_INCREMENT NOT NULL,
            token VARCHAR(128) NOT NULL,
            user_uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            user_agent VARCHAR(255) DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            UNIQUE INDEX UNIQ_9BACE7E15F37A13B (token),
            INDEX idx_refresh_token (token),
            INDEX idx_refresh_token_user (user_uuid),
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE refresh_tokens ADD CONSTRAINT FK_REFRESH_TOKENS_USER FOREIGN KEY (user_uuid) REFERENCES users (uuid) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE refresh_tokens DROP FOREIGN KEY FK_REFRESH_TOKENS_USER');
        $this->addSql('DROP TABLE refresh_tokens');
    }
}
