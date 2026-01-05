<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fix DateTimeImmutable columns in playthroughs table
 */
final class Version20260102190200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add COMMENT to datetime columns in playthroughs to fix DateTimeImmutable conversion';
    }

    public function up(Schema $schema): void
    {
        // Add Doctrine type comments to all datetime columns in playthroughs
        $this->addSql('ALTER TABLE playthroughs MODIFY started_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE playthroughs MODIFY ended_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE playthroughs MODIFY paused_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE playthroughs MODIFY created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE playthroughs MODIFY last_pick_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // Remove type comments
        $this->addSql('ALTER TABLE playthroughs MODIFY started_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE playthroughs MODIFY ended_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE playthroughs MODIFY paused_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE playthroughs MODIFY created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE playthroughs MODIFY last_pick_at DATETIME DEFAULT NULL');
    }
}

