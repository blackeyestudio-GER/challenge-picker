<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251222004000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ruleset favorites and voting tables';
    }

    public function up(Schema $schema): void
    {
        // Create ruleset_votes table
        $this->addSql('CREATE TABLE ruleset_votes (id INT AUTO_INCREMENT NOT NULL, user_uuid VARCHAR(36) NOT NULL, ruleset_id INT NOT NULL, vote_type SMALLINT NOT NULL DEFAULT 1, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_27462065ABFE1C6F (user_uuid), INDEX IDX_2746206554F1C144 (ruleset_id), UNIQUE INDEX unique_user_ruleset (user_uuid, ruleset_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ruleset_votes ADD CONSTRAINT FK_27462065ABFE1C6F FOREIGN KEY (user_uuid) REFERENCES users (uuid) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ruleset_votes ADD CONSTRAINT FK_2746206554F1C144 FOREIGN KEY (ruleset_id) REFERENCES rulesets (id) ON DELETE CASCADE');
        
        // Create user_favorite_rulesets table
        $this->addSql('CREATE TABLE user_favorite_rulesets (id INT AUTO_INCREMENT NOT NULL, user_uuid VARCHAR(36) NOT NULL, ruleset_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_FC62B803ABFE1C6F (user_uuid), INDEX IDX_FC62B80354F1C144 (ruleset_id), UNIQUE INDEX unique_user_ruleset (user_uuid, ruleset_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_favorite_rulesets ADD CONSTRAINT FK_FC62B803ABFE1C6F FOREIGN KEY (user_uuid) REFERENCES users (uuid) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_favorite_rulesets ADD CONSTRAINT FK_FC62B80354F1C144 FOREIGN KEY (ruleset_id) REFERENCES rulesets (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ruleset_votes DROP FOREIGN KEY FK_27462065ABFE1C6F');
        $this->addSql('ALTER TABLE ruleset_votes DROP FOREIGN KEY FK_2746206554F1C144');
        $this->addSql('ALTER TABLE user_favorite_rulesets DROP FOREIGN KEY FK_FC62B803ABFE1C6F');
        $this->addSql('ALTER TABLE user_favorite_rulesets DROP FOREIGN KEY FK_FC62B80354F1C144');
        $this->addSql('DROP TABLE ruleset_votes');
        $this->addSql('DROP TABLE user_favorite_rulesets');
    }
}
