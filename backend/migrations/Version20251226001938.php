<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251226001938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ruleset_games (ruleset_id INT NOT NULL, game_id INT NOT NULL, INDEX IDX_89EE719B54F1C144 (ruleset_id), INDEX IDX_89EE719BE48FD905 (game_id), PRIMARY KEY (ruleset_id, game_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ruleset_games ADD CONSTRAINT FK_89EE719B54F1C144 FOREIGN KEY (ruleset_id) REFERENCES rulesets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ruleset_games ADD CONSTRAINT FK_89EE719BE48FD905 FOREIGN KEY (game_id) REFERENCES games (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE categories RENAME INDEX uniq_3af34668a1c01850 TO UNIQ_3AF34668AC925A07');
        $this->addSql('ALTER TABLE game_category_votes CHANGE user_uuid user_uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE playthroughs CHANGE uuid uuid BINARY(16) NOT NULL, CHANGE user_uuid user_uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE ruleset_votes CHANGE user_uuid user_uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE rulesets DROP FOREIGN KEY `FK_AE2A1BD9E48FD905`');
        $this->addSql('DROP INDEX IDX_AE2A1BD9E48FD905 ON rulesets');
        $this->addSql('ALTER TABLE rulesets DROP game_id');
        $this->addSql('ALTER TABLE user_favorite_games CHANGE user_uuid user_uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE user_favorite_rulesets CHANGE user_uuid user_uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE user_obs_preferences CHANGE user_uuid user_uuid BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE users CHANGE uuid uuid BINARY(16) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ruleset_games DROP FOREIGN KEY FK_89EE719B54F1C144');
        $this->addSql('ALTER TABLE ruleset_games DROP FOREIGN KEY FK_89EE719BE48FD905');
        $this->addSql('DROP TABLE ruleset_games');
        $this->addSql('ALTER TABLE categories RENAME INDEX uniq_3af34668ac925a07 TO UNIQ_3AF34668A1C01850');
        $this->addSql('ALTER TABLE game_category_votes CHANGE user_uuid user_uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE playthroughs CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE user_uuid user_uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE ruleset_votes CHANGE user_uuid user_uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE rulesets ADD game_id INT NOT NULL');
        $this->addSql('ALTER TABLE rulesets ADD CONSTRAINT `FK_AE2A1BD9E48FD905` FOREIGN KEY (game_id) REFERENCES games (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_AE2A1BD9E48FD905 ON rulesets (game_id)');
        $this->addSql('ALTER TABLE user_favorite_games CHANGE user_uuid user_uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_favorite_rulesets CHANGE user_uuid user_uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_obs_preferences CHANGE user_uuid user_uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE users CHANGE uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }
}
