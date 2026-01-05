<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add rule_cooldown_seconds to playthroughs table
 */
final class Version20260102234000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add rule_cooldown_seconds field to playthroughs table (default 120 seconds)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE playthroughs ADD rule_cooldown_seconds INT DEFAULT 120 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE playthroughs DROP rule_cooldown_seconds');
    }
}

