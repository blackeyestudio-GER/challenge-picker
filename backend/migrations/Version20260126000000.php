<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add isTemplate field to rulesets table for template/popular rulesets
 */
final class Version20260126000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add isTemplate field to rulesets table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rulesets ADD is_template TINYINT(1) DEFAULT 0 NOT NULL');
        
        // Mark popular rulesets as templates
        $this->addSql("UPDATE rulesets SET is_template = 1 WHERE name IN (
            'Resident Evil: Knife Only Hardcore',
            'Dark Souls: Soul Level 1 Challenge',
            'Zelda: Three Heart Hero'
        )");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rulesets DROP is_template');
    }
}
