<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fix DateTimeImmutable columns in playthrough_rules table
 */
final class Version20260102190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add COMMENT to datetime columns in playthrough_rules to fix DateTimeImmutable conversion';
    }

    public function up(Schema $schema): void
    {
        // Add Doctrine type comments to datetime columns so they're properly converted to DateTimeImmutable
        $this->addSql('ALTER TABLE playthrough_rules MODIFY expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE playthrough_rules MODIFY started_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE playthrough_rules MODIFY completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE playthrough_rules MODIFY created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // Remove type comments
        $this->addSql('ALTER TABLE playthrough_rules MODIFY expires_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE playthrough_rules MODIFY started_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE playthrough_rules MODIFY completed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE playthrough_rules MODIFY created_at DATETIME NOT NULL');
    }
}

