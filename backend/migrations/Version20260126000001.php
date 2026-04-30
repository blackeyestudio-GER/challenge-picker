<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add artist support and payout request system
 */
final class Version20260126000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add artist support: isArtist field to users, create payout_requests table';
    }

    public function up(Schema $schema): void
    {
        // Add isArtist field to users table
        $this->addSql('ALTER TABLE users ADD is_artist TINYINT(1) DEFAULT 0 NOT NULL');

        // Create payout_requests table
        $this->addSql('CREATE TABLE payout_requests (
            id INT AUTO_INCREMENT NOT NULL,
            designer_uuid BINARY(16) NOT NULL,
            amount NUMERIC(10, 2) NOT NULL,
            currency VARCHAR(3) DEFAULT \'USD\' NOT NULL,
            status VARCHAR(20) DEFAULT \'pending\' NOT NULL,
            requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            processed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            admin_notes LONGTEXT DEFAULT NULL,
            processed_by_uuid BINARY(16) DEFAULT NULL,
            INDEX IDX_PAYOUT_REQUESTS_DESIGNER (designer_uuid),
            INDEX IDX_PAYOUT_REQUESTS_PROCESSED_BY (processed_by_uuid),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE payout_requests ADD CONSTRAINT FK_PAYOUT_REQUESTS_DESIGNER FOREIGN KEY (designer_uuid) REFERENCES users (uuid) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE payout_requests ADD CONSTRAINT FK_PAYOUT_REQUESTS_PROCESSED_BY FOREIGN KEY (processed_by_uuid) REFERENCES users (uuid) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE payout_requests');
        $this->addSql('ALTER TABLE users DROP is_artist');
    }
}
