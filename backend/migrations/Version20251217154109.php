<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217154109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_obs_preferences (id INT AUTO_INCREMENT NOT NULL, show_timer_in_setup TINYINT DEFAULT 1 NOT NULL, show_timer_in_active TINYINT DEFAULT 1 NOT NULL, show_timer_in_paused TINYINT DEFAULT 1 NOT NULL, show_timer_in_completed TINYINT DEFAULT 0 NOT NULL, show_rules_in_setup TINYINT DEFAULT 0 NOT NULL, show_rules_in_active TINYINT DEFAULT 1 NOT NULL, show_rules_in_paused TINYINT DEFAULT 1 NOT NULL, show_rules_in_completed TINYINT DEFAULT 0 NOT NULL, show_status_in_setup TINYINT DEFAULT 1 NOT NULL, show_status_in_active TINYINT DEFAULT 1 NOT NULL, show_status_in_paused TINYINT DEFAULT 1 NOT NULL, show_status_in_completed TINYINT DEFAULT 1 NOT NULL, timer_position VARCHAR(20) DEFAULT \'none\' NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_756D60BCA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user_obs_preferences ADD CONSTRAINT FK_756D60BCA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE games CHANGE description description LONGTEXT DEFAULT NULL, CHANGE image image LONGTEXT DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX IDX_PLAYTHROUGH_RULES_COMPLETED ON playthrough_rules');
        $this->addSql('DROP INDEX IDX_PLAYTHROUGH_RULES_ACTIVE ON playthrough_rules');
        $this->addSql('ALTER TABLE playthrough_rules CHANGE is_active is_active TINYINT NOT NULL, CHANGE started_at started_at DATETIME DEFAULT NULL, CHANGE completed_at completed_at DATETIME DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE playthrough_rules RENAME INDEX idx_playthrough_rules_playthrough TO IDX_3456F21D5F8BD68');
        $this->addSql('ALTER TABLE playthrough_rules RENAME INDEX idx_playthrough_rules_rule TO IDX_3456F21D744E0351');
        $this->addSql('DROP INDEX IDX_PLAYTHROUGHS_STATUS ON playthroughs');
        $this->addSql('ALTER TABLE playthroughs CHANGE started_at started_at DATETIME DEFAULT NULL, CHANGE ended_at ended_at DATETIME DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE playthroughs RENAME INDEX uniq_playthroughs_uuid TO UNIQ_DFEEC438D17F50A6');
        $this->addSql('ALTER TABLE playthroughs RENAME INDEX idx_playthroughs_user TO IDX_DFEEC438A76ED395');
        $this->addSql('ALTER TABLE playthroughs RENAME INDEX idx_playthroughs_game TO IDX_DFEEC438E48FD905');
        $this->addSql('ALTER TABLE playthroughs RENAME INDEX idx_playthroughs_ruleset TO IDX_DFEEC43854F1C144');
        $this->addSql('ALTER TABLE rules CHANGE text text LONGTEXT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE rules RENAME INDEX idx_rules_ruleset TO IDX_899A993C54F1C144');
        $this->addSql('ALTER TABLE rulesets CHANGE description description LONGTEXT DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE rulesets RENAME INDEX idx_rulesets_game TO IDX_AE2A1BD9E48FD905');
        $this->addSql('ALTER TABLE users CHANGE avatar avatar LONGTEXT DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_obs_preferences DROP FOREIGN KEY FK_756D60BCA76ED395');
        $this->addSql('DROP TABLE user_obs_preferences');
        $this->addSql('ALTER TABLE games CHANGE description description TEXT DEFAULT NULL, CHANGE image image TEXT DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE playthrough_rules CHANGE is_active is_active TINYINT DEFAULT 1 NOT NULL, CHANGE started_at started_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE completed_at completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX IDX_PLAYTHROUGH_RULES_COMPLETED ON playthrough_rules (completed_at)');
        $this->addSql('CREATE INDEX IDX_PLAYTHROUGH_RULES_ACTIVE ON playthrough_rules (is_active)');
        $this->addSql('ALTER TABLE playthrough_rules RENAME INDEX idx_3456f21d744e0351 TO IDX_PLAYTHROUGH_RULES_RULE');
        $this->addSql('ALTER TABLE playthrough_rules RENAME INDEX idx_3456f21d5f8bd68 TO IDX_PLAYTHROUGH_RULES_PLAYTHROUGH');
        $this->addSql('ALTER TABLE playthroughs CHANGE started_at started_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE ended_at ended_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX IDX_PLAYTHROUGHS_STATUS ON playthroughs (status)');
        $this->addSql('ALTER TABLE playthroughs RENAME INDEX uniq_dfeec438d17f50a6 TO UNIQ_PLAYTHROUGHS_UUID');
        $this->addSql('ALTER TABLE playthroughs RENAME INDEX idx_dfeec43854f1c144 TO IDX_PLAYTHROUGHS_RULESET');
        $this->addSql('ALTER TABLE playthroughs RENAME INDEX idx_dfeec438e48fd905 TO IDX_PLAYTHROUGHS_GAME');
        $this->addSql('ALTER TABLE playthroughs RENAME INDEX idx_dfeec438a76ed395 TO IDX_PLAYTHROUGHS_USER');
        $this->addSql('ALTER TABLE rules CHANGE text text TEXT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE rules RENAME INDEX idx_899a993c54f1c144 TO IDX_RULES_RULESET');
        $this->addSql('ALTER TABLE rulesets CHANGE description description TEXT DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE rulesets RENAME INDEX idx_ae2a1bd9e48fd905 TO IDX_RULESETS_GAME');
        $this->addSql('ALTER TABLE users CHANGE avatar avatar TEXT DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
