<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add store and Twitch links to games table
 */
final class Version20251222214800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add steam_link, epic_link, gog_link, and twitch_category columns to games table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE games ADD steam_link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE games ADD epic_link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE games ADD gog_link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE games ADD twitch_category VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE games DROP steam_link');
        $this->addSql('ALTER TABLE games DROP epic_link');
        $this->addSql('ALTER TABLE games DROP gog_link');
        $this->addSql('ALTER TABLE games DROP twitch_category');
    }
}

