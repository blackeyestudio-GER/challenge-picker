<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260102095506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add allow_viewer_picks column to playthroughs table with default value false';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE playthroughs ADD allow_viewer_picks TINYINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE playthroughs DROP allow_viewer_picks');
    }
}
