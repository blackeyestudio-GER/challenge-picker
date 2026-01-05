<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260104162942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE design_sets ADD icon_color VARCHAR(7) DEFAULT NULL, ADD icon_brightness NUMERIC(3, 2) DEFAULT NULL, ADD icon_opacity NUMERIC(3, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE playthroughs CHANGE id id INT NOT NULL, CHANGE max_concurrent_rules max_concurrent_rules INT NOT NULL, CHANGE status status VARCHAR(20) NOT NULL, CHANGE started_at started_at DATETIME DEFAULT NULL, CHANGE ended_at ended_at DATETIME DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE paused_at paused_at DATETIME DEFAULT NULL, CHANGE last_pick_at last_pick_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE rules DROP icon_color, DROP icon_brightness, DROP icon_opacity');
        $this->addSql('ALTER TABLE users CHANGE refresh_token_expires_at refresh_token_expires_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE design_sets DROP icon_color, DROP icon_brightness, DROP icon_opacity');
        $this->addSql('ALTER TABLE playthroughs CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE max_concurrent_rules max_concurrent_rules INT DEFAULT 3 NOT NULL, CHANGE status status VARCHAR(20) DEFAULT \'setup\' NOT NULL, CHANGE started_at started_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE ended_at ended_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE paused_at paused_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE last_pick_at last_pick_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE rules ADD icon_color VARCHAR(7) DEFAULT NULL, ADD icon_brightness NUMERIC(3, 2) DEFAULT NULL, ADD icon_opacity NUMERIC(3, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE users CHANGE refresh_token_expires_at refresh_token_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
