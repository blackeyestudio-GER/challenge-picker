<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add require_auth field to playthroughs table
 */
final class Version20260102050800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add require_auth field to playthroughs table for privacy control';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE playthroughs ADD require_auth TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE playthroughs DROP require_auth');
    }
}

