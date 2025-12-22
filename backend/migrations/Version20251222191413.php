<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251222191413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card_designs (id INT AUTO_INCREMENT NOT NULL, card_identifier VARCHAR(50) NOT NULL, image_base64 LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, design_set_id INT NOT NULL, INDEX IDX_6A003A8BB23A9D1B (design_set_id), UNIQUE INDEX unique_card_per_set (design_set_id, card_identifier), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE design_names (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_54D238635E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE design_sets (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, design_name_id INT NOT NULL, INDEX IDX_C9AB0174E8BC0CFC (design_name_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE card_designs ADD CONSTRAINT FK_6A003A8BB23A9D1B FOREIGN KEY (design_set_id) REFERENCES design_sets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE design_sets ADD CONSTRAINT FK_C9AB0174E8BC0CFC FOREIGN KEY (design_name_id) REFERENCES design_names (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_categories DROP FOREIGN KEY `FK_5E4D1F0012469DE2`');
        $this->addSql('ALTER TABLE game_categories DROP FOREIGN KEY `FK_5E4D1F00E48FD905`');
        $this->addSql('DROP TABLE game_categories');
        $this->addSql('ALTER TABLE game_category_votes CHANGE vote_type vote_type SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE ruleset_votes DROP FOREIGN KEY `FK_2746206554F1C144`');
        $this->addSql('ALTER TABLE ruleset_votes DROP FOREIGN KEY `FK_27462065ABFE1C6F`');
        $this->addSql('ALTER TABLE ruleset_votes CHANGE vote_type vote_type SMALLINT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE ruleset_votes ADD CONSTRAINT FK_2746206554F1C144 FOREIGN KEY (ruleset_id) REFERENCES rulesets (id)');
        $this->addSql('ALTER TABLE ruleset_votes ADD CONSTRAINT FK_27462065ABFE1C6F FOREIGN KEY (user_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE user_favorite_games DROP FOREIGN KEY `FK_222EDF4CABFE1C6F`');
        $this->addSql('ALTER TABLE user_favorite_games DROP FOREIGN KEY `FK_222EDF4CE48FD905`');
        $this->addSql('ALTER TABLE user_favorite_games CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user_favorite_games ADD CONSTRAINT FK_222EDF4CABFE1C6F FOREIGN KEY (user_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE user_favorite_games ADD CONSTRAINT FK_222EDF4CE48FD905 FOREIGN KEY (game_id) REFERENCES games (id)');
        $this->addSql('ALTER TABLE user_favorite_rulesets DROP FOREIGN KEY `FK_FC62B80354F1C144`');
        $this->addSql('ALTER TABLE user_favorite_rulesets DROP FOREIGN KEY `FK_FC62B803ABFE1C6F`');
        $this->addSql('ALTER TABLE user_favorite_rulesets CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user_favorite_rulesets ADD CONSTRAINT FK_FC62B80354F1C144 FOREIGN KEY (ruleset_id) REFERENCES rulesets (id)');
        $this->addSql('ALTER TABLE user_favorite_rulesets ADD CONSTRAINT FK_FC62B803ABFE1C6F FOREIGN KEY (user_uuid) REFERENCES users (uuid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE game_categories (id INT AUTO_INCREMENT NOT NULL, game_id INT NOT NULL, category_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_5E4D1F0012469DE2 (category_id), INDEX IDX_5E4D1F00E48FD905 (game_id), UNIQUE INDEX unique_game_category (game_id, category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE game_categories ADD CONSTRAINT `FK_5E4D1F0012469DE2` FOREIGN KEY (category_id) REFERENCES categories (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_categories ADD CONSTRAINT `FK_5E4D1F00E48FD905` FOREIGN KEY (game_id) REFERENCES games (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card_designs DROP FOREIGN KEY FK_6A003A8BB23A9D1B');
        $this->addSql('ALTER TABLE design_sets DROP FOREIGN KEY FK_C9AB0174E8BC0CFC');
        $this->addSql('DROP TABLE card_designs');
        $this->addSql('DROP TABLE design_names');
        $this->addSql('DROP TABLE design_sets');
        $this->addSql('ALTER TABLE game_category_votes CHANGE vote_type vote_type SMALLINT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE ruleset_votes DROP FOREIGN KEY FK_27462065ABFE1C6F');
        $this->addSql('ALTER TABLE ruleset_votes DROP FOREIGN KEY FK_2746206554F1C144');
        $this->addSql('ALTER TABLE ruleset_votes CHANGE vote_type vote_type SMALLINT DEFAULT 1 NOT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE ruleset_votes ADD CONSTRAINT `FK_27462065ABFE1C6F` FOREIGN KEY (user_uuid) REFERENCES users (uuid) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ruleset_votes ADD CONSTRAINT `FK_2746206554F1C144` FOREIGN KEY (ruleset_id) REFERENCES rulesets (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_favorite_games DROP FOREIGN KEY FK_222EDF4CABFE1C6F');
        $this->addSql('ALTER TABLE user_favorite_games DROP FOREIGN KEY FK_222EDF4CE48FD905');
        $this->addSql('ALTER TABLE user_favorite_games CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user_favorite_games ADD CONSTRAINT `FK_222EDF4CABFE1C6F` FOREIGN KEY (user_uuid) REFERENCES users (uuid) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_favorite_games ADD CONSTRAINT `FK_222EDF4CE48FD905` FOREIGN KEY (game_id) REFERENCES games (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_favorite_rulesets DROP FOREIGN KEY FK_FC62B803ABFE1C6F');
        $this->addSql('ALTER TABLE user_favorite_rulesets DROP FOREIGN KEY FK_FC62B80354F1C144');
        $this->addSql('ALTER TABLE user_favorite_rulesets CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user_favorite_rulesets ADD CONSTRAINT `FK_FC62B803ABFE1C6F` FOREIGN KEY (user_uuid) REFERENCES users (uuid) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_favorite_rulesets ADD CONSTRAINT `FK_FC62B80354F1C144` FOREIGN KEY (ruleset_id) REFERENCES rulesets (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
