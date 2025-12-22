<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251222225159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add rule difficulty levels and ruleset rule card mappings';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rule_difficulty_levels (id INT AUTO_INCREMENT NOT NULL, difficulty_level SMALLINT NOT NULL, duration_minutes INT NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, rule_id INT NOT NULL, INDEX IDX_84F9BA34744E0351 (rule_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE ruleset_rule_cards (id INT AUTO_INCREMENT NOT NULL, position SMALLINT NOT NULL, created_at DATETIME NOT NULL, ruleset_id INT NOT NULL, rule_id INT NOT NULL, tarot_card_identifier VARCHAR(50) NOT NULL, INDEX IDX_B4C5CBAF54F1C144 (ruleset_id), INDEX IDX_B4C5CBAF744E0351 (rule_id), INDEX IDX_B4C5CBAFB11DE030 (tarot_card_identifier), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE rule_difficulty_levels ADD CONSTRAINT FK_84F9BA34744E0351 FOREIGN KEY (rule_id) REFERENCES rules (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ruleset_rule_cards ADD CONSTRAINT FK_B4C5CBAF54F1C144 FOREIGN KEY (ruleset_id) REFERENCES rulesets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ruleset_rule_cards ADD CONSTRAINT FK_B4C5CBAF744E0351 FOREIGN KEY (rule_id) REFERENCES rules (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ruleset_rule_cards ADD CONSTRAINT FK_B4C5CBAFB11DE030 FOREIGN KEY (tarot_card_identifier) REFERENCES tarot_cards (identifier) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_categories DROP FOREIGN KEY `FK_5E4D1F0012469DE2`');
        $this->addSql('ALTER TABLE game_categories DROP FOREIGN KEY `FK_5E4D1F00E48FD905`');
        $this->addSql('ALTER TABLE ruleset_rules DROP FOREIGN KEY `FK_FF57C39654F1C144`');
        $this->addSql('ALTER TABLE ruleset_rules DROP FOREIGN KEY `FK_FF57C396744E0351`');
        $this->addSql('DROP TABLE game_categories');
        $this->addSql('DROP TABLE ruleset_rules');
        $this->addSql('ALTER TABLE rules ADD rule_type VARCHAR(20) NOT NULL, DROP duration_minutes');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE game_categories (game_id INT NOT NULL, category_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_5E4D1F0012469DE2 (category_id), INDEX IDX_5E4D1F00E48FD905 (game_id), PRIMARY KEY (game_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ruleset_rules (ruleset_id INT NOT NULL, rule_id INT NOT NULL, INDEX IDX_FF57C39654F1C144 (ruleset_id), INDEX IDX_FF57C396744E0351 (rule_id), PRIMARY KEY (ruleset_id, rule_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE game_categories ADD CONSTRAINT `FK_5E4D1F0012469DE2` FOREIGN KEY (category_id) REFERENCES categories (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_categories ADD CONSTRAINT `FK_5E4D1F00E48FD905` FOREIGN KEY (game_id) REFERENCES games (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ruleset_rules ADD CONSTRAINT `FK_FF57C39654F1C144` FOREIGN KEY (ruleset_id) REFERENCES rulesets (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE ruleset_rules ADD CONSTRAINT `FK_FF57C396744E0351` FOREIGN KEY (rule_id) REFERENCES rules (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE rule_difficulty_levels DROP FOREIGN KEY FK_84F9BA34744E0351');
        $this->addSql('ALTER TABLE ruleset_rule_cards DROP FOREIGN KEY FK_B4C5CBAF54F1C144');
        $this->addSql('ALTER TABLE ruleset_rule_cards DROP FOREIGN KEY FK_B4C5CBAF744E0351');
        $this->addSql('ALTER TABLE ruleset_rule_cards DROP FOREIGN KEY FK_B4C5CBAFB11DE030');
        $this->addSql('DROP TABLE rule_difficulty_levels');
        $this->addSql('DROP TABLE ruleset_rule_cards');
        $this->addSql('ALTER TABLE rules ADD duration_minutes INT NOT NULL, DROP rule_type');
    }
}
