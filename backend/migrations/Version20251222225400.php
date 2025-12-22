<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add unique constraint to ensure each rule has unique difficulty levels
 */
final class Version20251222225400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique constraint on rule_difficulty_levels (rule_id, difficulty_level)';
    }

    public function up(Schema $schema): void
    {
        // Add unique constraint to prevent duplicate difficulty levels per rule
        $this->addSql('ALTER TABLE rule_difficulty_levels ADD UNIQUE INDEX UNIQ_RULE_DIFFICULTY (rule_id, difficulty_level)');
    }

    public function down(Schema $schema): void
    {
        // Remove unique constraint
        $this->addSql('ALTER TABLE rule_difficulty_levels DROP INDEX UNIQ_RULE_DIFFICULTY');
    }
}

