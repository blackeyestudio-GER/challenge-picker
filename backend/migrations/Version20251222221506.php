<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251222221506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Refactor rules to have many-to-many relationship with rulesets';
    }

    public function up(Schema $schema): void
    {
        // Step 1: Create the junction table
        $this->addSql('CREATE TABLE ruleset_rules (ruleset_id INT NOT NULL, rule_id INT NOT NULL, INDEX IDX_FF57C39654F1C144 (ruleset_id), INDEX IDX_FF57C396744E0351 (rule_id), PRIMARY KEY (ruleset_id, rule_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ruleset_rules ADD CONSTRAINT FK_FF57C39654F1C144 FOREIGN KEY (ruleset_id) REFERENCES rulesets (id)');
        $this->addSql('ALTER TABLE ruleset_rules ADD CONSTRAINT FK_FF57C396744E0351 FOREIGN KEY (rule_id) REFERENCES rules (id)');
        
        // Step 2: Migrate existing rule-ruleset associations to the junction table
        $this->addSql('INSERT INTO ruleset_rules (ruleset_id, rule_id) SELECT ruleset_id, id FROM rules');
        
        // Step 3: Add new columns (name, description) with temporary NULL constraint
        $this->addSql('ALTER TABLE rules ADD name VARCHAR(255) DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL');
        
        // Step 4: Migrate text column to name column
        $this->addSql('UPDATE rules SET name = SUBSTRING(text, 1, 255), description = text');
        
        // Step 5: Make name column NOT NULL
        $this->addSql('ALTER TABLE rules MODIFY name VARCHAR(255) NOT NULL');
        
        // Step 6: Drop the old foreign key and text column
        $this->addSql('ALTER TABLE rules DROP FOREIGN KEY `FK_899A993C54F1C144`');
        $this->addSql('DROP INDEX IDX_899A993C54F1C144 ON rules');
        $this->addSql('ALTER TABLE rules DROP text, DROP ruleset_id');
    }

    public function down(Schema $schema): void
    {
        // Step 1: Add back the ruleset_id column and text column
        $this->addSql('ALTER TABLE rules ADD text LONGTEXT DEFAULT NULL, ADD ruleset_id INT DEFAULT NULL');
        
        // Step 2: Migrate data back (copy from first ruleset association, or NULL if none)
        $this->addSql('UPDATE rules r LEFT JOIN ruleset_rules rr ON rr.rule_id = r.id SET r.text = COALESCE(r.description, r.name), r.ruleset_id = rr.ruleset_id');
        
        // Step 3: Make columns NOT NULL
        $this->addSql('ALTER TABLE rules MODIFY text LONGTEXT NOT NULL, MODIFY ruleset_id INT NOT NULL');
        
        // Step 4: Add foreign key constraint back
        $this->addSql('ALTER TABLE rules ADD CONSTRAINT `FK_899A993C54F1C144` FOREIGN KEY (ruleset_id) REFERENCES rulesets (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_899A993C54F1C144 ON rules (ruleset_id)');
        
        // Step 5: Drop the junction table
        $this->addSql('ALTER TABLE ruleset_rules DROP FOREIGN KEY FK_FF57C39654F1C144');
        $this->addSql('ALTER TABLE ruleset_rules DROP FOREIGN KEY FK_FF57C396744E0351');
        $this->addSql('DROP TABLE ruleset_rules');
        
        // Step 6: Drop name and description columns
        $this->addSql('ALTER TABLE rules DROP name, DROP description');
    }
}
