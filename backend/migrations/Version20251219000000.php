<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial database schema v1.0.0
 * Creates all tables for the Challenge Picker application
 */
final class Version20251219000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial database schema v1.0.0 - Creates all tables';
    }

    public function up(Schema $schema): void
    {
        // Create games table
        $this->addSql('CREATE TABLE games (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, image LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');

        // Create users table
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, username VARCHAR(50) NOT NULL, password VARCHAR(255) DEFAULT NULL, oauth_provider VARCHAR(20) DEFAULT NULL, oauth_id VARCHAR(255) DEFAULT NULL, avatar LONGTEXT DEFAULT NULL, roles JSON NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), UNIQUE INDEX UNIQ_1483A5E9F85E0677 (username), INDEX IDX_1483A5E9_OAUTH (oauth_provider, oauth_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');

        // Create user_obs_preferences table
        $this->addSql('CREATE TABLE user_obs_preferences (id INT AUTO_INCREMENT NOT NULL, show_timer_in_setup TINYINT DEFAULT 0 NOT NULL, show_timer_in_active TINYINT DEFAULT 1 NOT NULL, show_timer_in_paused TINYINT DEFAULT 1 NOT NULL, show_timer_in_completed TINYINT DEFAULT 0 NOT NULL, show_status_in_setup TINYINT DEFAULT 1 NOT NULL, show_status_in_active TINYINT DEFAULT 1 NOT NULL, show_status_in_paused TINYINT DEFAULT 1 NOT NULL, show_status_in_completed TINYINT DEFAULT 1 NOT NULL, timer_position VARCHAR(20) DEFAULT \'none\' NOT NULL, timer_design VARCHAR(20) DEFAULT \'numbers\' NOT NULL, status_design VARCHAR(20) DEFAULT \'word\' NOT NULL, rules_design VARCHAR(20) DEFAULT \'list\' NOT NULL, chroma_key_color VARCHAR(9) DEFAULT \'#00FF00\' NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_756D60BCA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');

        // Create rulesets table
        $this->addSql('CREATE TABLE rulesets (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, game_id INT NOT NULL, INDEX IDX_AE2A1BD9E48FD905 (game_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');

        // Create rules table
        $this->addSql('CREATE TABLE rules (id INT AUTO_INCREMENT NOT NULL, text LONGTEXT NOT NULL, duration_minutes INT NOT NULL, created_at DATETIME NOT NULL, ruleset_id INT NOT NULL, INDEX IDX_899A993C54F1C144 (ruleset_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');

        // Create playthroughs table
        $this->addSql('CREATE TABLE playthroughs (id INT AUTO_INCREMENT NOT NULL, uuid VARCHAR(36) NOT NULL, max_concurrent_rules INT NOT NULL, status VARCHAR(20) NOT NULL, started_at DATETIME DEFAULT NULL, ended_at DATETIME DEFAULT NULL, total_duration INT DEFAULT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, game_id INT NOT NULL, ruleset_id INT NOT NULL, UNIQUE INDEX UNIQ_DFEEC438D17F50A6 (uuid), INDEX IDX_DFEEC438A76ED395 (user_id), INDEX IDX_DFEEC438E48FD905 (game_id), INDEX IDX_DFEEC43854F1C144 (ruleset_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');

        // Create playthrough_rules table
        $this->addSql('CREATE TABLE playthrough_rules (id INT AUTO_INCREMENT NOT NULL, is_active TINYINT NOT NULL, started_at DATETIME DEFAULT NULL, completed_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, playthrough_id INT NOT NULL, rule_id INT NOT NULL, INDEX IDX_3456F21D5F8BD68 (playthrough_id), INDEX IDX_3456F21D744E0351 (rule_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');

        // Add foreign keys
        $this->addSql('ALTER TABLE user_obs_preferences ADD CONSTRAINT FK_756D60BCA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE rulesets ADD CONSTRAINT FK_AE2A1BD9E48FD905 FOREIGN KEY (game_id) REFERENCES games (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rules ADD CONSTRAINT FK_899A993C54F1C144 FOREIGN KEY (ruleset_id) REFERENCES rulesets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE playthroughs ADD CONSTRAINT FK_DFEEC438A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE playthroughs ADD CONSTRAINT FK_DFEEC438E48FD905 FOREIGN KEY (game_id) REFERENCES games (id)');
        $this->addSql('ALTER TABLE playthroughs ADD CONSTRAINT FK_DFEEC43854F1C144 FOREIGN KEY (ruleset_id) REFERENCES rulesets (id)');
        $this->addSql('ALTER TABLE playthrough_rules ADD CONSTRAINT FK_3456F21D5F8BD68 FOREIGN KEY (playthrough_id) REFERENCES playthroughs (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE playthrough_rules ADD CONSTRAINT FK_3456F21D744E0351 FOREIGN KEY (rule_id) REFERENCES rules (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign keys first
        $this->addSql('ALTER TABLE user_obs_preferences DROP FOREIGN KEY FK_756D60BCA76ED395');
        $this->addSql('ALTER TABLE rulesets DROP FOREIGN KEY FK_AE2A1BD9E48FD905');
        $this->addSql('ALTER TABLE rules DROP FOREIGN KEY FK_899A993C54F1C144');
        $this->addSql('ALTER TABLE playthroughs DROP FOREIGN KEY FK_DFEEC438A76ED395');
        $this->addSql('ALTER TABLE playthroughs DROP FOREIGN KEY FK_DFEEC438E48FD905');
        $this->addSql('ALTER TABLE playthroughs DROP FOREIGN KEY FK_DFEEC43854F1C144');
        $this->addSql('ALTER TABLE playthrough_rules DROP FOREIGN KEY FK_3456F21D5F8BD68');
        $this->addSql('ALTER TABLE playthrough_rules DROP FOREIGN KEY FK_3456F21D744E0351');

        // Drop tables
        $this->addSql('DROP TABLE playthrough_rules');
        $this->addSql('DROP TABLE playthroughs');
        $this->addSql('DROP TABLE rules');
        $this->addSql('DROP TABLE rulesets');
        $this->addSql('DROP TABLE user_obs_preferences');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE games');
    }
}

