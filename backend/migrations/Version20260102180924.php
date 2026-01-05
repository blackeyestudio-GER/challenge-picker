<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260102180924 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add last_pick_at and cooldown_rule_ids to playthroughs table for backend state management';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE playthroughs ADD last_pick_at DATETIME DEFAULT NULL, ADD cooldown_rule_ids JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE playthroughs DROP last_pick_at, DROP cooldown_rule_ids');
    }
}
