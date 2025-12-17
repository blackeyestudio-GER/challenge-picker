<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Game System Migration: Creates tables for games, rulesets, rules, playthroughs, and playthrough_rules
 */
final class Version20241217000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates game system tables for challenge picker';
    }

    public function up(Schema $schema): void
    {
        // Games table
        $this->addSql('CREATE TABLE games (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            image VARCHAR(500) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Rulesets table
        $this->addSql('CREATE TABLE rulesets (
            id INT AUTO_INCREMENT NOT NULL,
            game_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_RULESETS_GAME (game_id),
            PRIMARY KEY(id),
            CONSTRAINT FK_RULESETS_GAME FOREIGN KEY (game_id) REFERENCES games (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Rules table
        $this->addSql('CREATE TABLE rules (
            id INT AUTO_INCREMENT NOT NULL,
            ruleset_id INT NOT NULL,
            text TEXT NOT NULL,
            duration_minutes INT NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_RULES_RULESET (ruleset_id),
            PRIMARY KEY(id),
            CONSTRAINT FK_RULES_RULESET FOREIGN KEY (ruleset_id) REFERENCES rulesets (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Playthroughs table
        $this->addSql('CREATE TABLE playthroughs (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            game_id INT NOT NULL,
            ruleset_id INT NOT NULL,
            uuid VARCHAR(36) NOT NULL,
            max_concurrent_rules INT NOT NULL,
            status VARCHAR(20) NOT NULL,
            started_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            ended_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            total_duration INT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_PLAYTHROUGHS_UUID (uuid),
            INDEX IDX_PLAYTHROUGHS_USER (user_id),
            INDEX IDX_PLAYTHROUGHS_GAME (game_id),
            INDEX IDX_PLAYTHROUGHS_RULESET (ruleset_id),
            INDEX IDX_PLAYTHROUGHS_STATUS (status),
            PRIMARY KEY(id),
            CONSTRAINT FK_PLAYTHROUGHS_USER FOREIGN KEY (user_id) REFERENCES users (id),
            CONSTRAINT FK_PLAYTHROUGHS_GAME FOREIGN KEY (game_id) REFERENCES games (id),
            CONSTRAINT FK_PLAYTHROUGHS_RULESET FOREIGN KEY (ruleset_id) REFERENCES rulesets (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Playthrough Rules table (junction table with additional metadata)
        $this->addSql('CREATE TABLE playthrough_rules (
            id INT AUTO_INCREMENT NOT NULL,
            playthrough_id INT NOT NULL,
            rule_id INT NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            started_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_PLAYTHROUGH_RULES_PLAYTHROUGH (playthrough_id),
            INDEX IDX_PLAYTHROUGH_RULES_RULE (rule_id),
            INDEX IDX_PLAYTHROUGH_RULES_ACTIVE (is_active),
            INDEX IDX_PLAYTHROUGH_RULES_COMPLETED (completed_at),
            PRIMARY KEY(id),
            CONSTRAINT FK_PLAYTHROUGH_RULES_PLAYTHROUGH FOREIGN KEY (playthrough_id) REFERENCES playthroughs (id) ON DELETE CASCADE,
            CONSTRAINT FK_PLAYTHROUGH_RULES_RULE FOREIGN KEY (rule_id) REFERENCES rules (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // Drop tables in reverse order (respect foreign keys)
        $this->addSql('DROP TABLE playthrough_rules');
        $this->addSql('DROP TABLE playthroughs');
        $this->addSql('DROP TABLE rules');
        $this->addSql('DROP TABLE rulesets');
        $this->addSql('DROP TABLE games');
    }
}

