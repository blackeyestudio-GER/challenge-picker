<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add is_active column to games table
 */
final class Version20251222220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_active column to games table for soft delete functionality';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE games ADD is_active TINYINT(1) DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE games DROP is_active');
    }
}

