<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251226102009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add current_amount and expires_at to playthrough_rules for counter and timer tracking';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE playthrough_rules ADD current_amount INT DEFAULT NULL, ADD expires_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE playthrough_rules DROP current_amount, DROP expires_at');
    }
}
