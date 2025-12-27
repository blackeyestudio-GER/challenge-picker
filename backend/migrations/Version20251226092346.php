<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251226092346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ruleset_default_rules (ruleset_id INT NOT NULL, rule_id INT NOT NULL, INDEX IDX_770988A54F1C144 (ruleset_id), INDEX IDX_770988A744E0351 (rule_id), PRIMARY KEY (ruleset_id, rule_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ruleset_default_rules ADD CONSTRAINT FK_770988A54F1C144 FOREIGN KEY (ruleset_id) REFERENCES rulesets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ruleset_default_rules ADD CONSTRAINT FK_770988A744E0351 FOREIGN KEY (rule_id) REFERENCES rules (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rule_difficulty_levels CHANGE duration_minutes duration_minutes INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ruleset_default_rules DROP FOREIGN KEY FK_770988A54F1C144');
        $this->addSql('ALTER TABLE ruleset_default_rules DROP FOREIGN KEY FK_770988A744E0351');
        $this->addSql('DROP TABLE ruleset_default_rules');
        $this->addSql('ALTER TABLE rule_difficulty_levels CHANGE duration_minutes duration_minutes INT NOT NULL');
    }
}
