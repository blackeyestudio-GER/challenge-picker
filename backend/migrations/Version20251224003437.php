<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial database schema for Challenge Picker application
 * Creates all tables for users, games, categories, rules, rulesets, playthroughs, and OBS overlays
 */
final class Version20251224003437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial database schema - creates all tables and relationships';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card_designs (id INT AUTO_INCREMENT NOT NULL, card_identifier VARCHAR(50) NOT NULL, image_base64 LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, design_set_id INT NOT NULL, INDEX IDX_6A003A8BB23A9D1B (design_set_id), UNIQUE INDEX unique_card_per_set (design_set_id, card_identifier), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, slug VARCHAR(50) NOT NULL, image LONGTEXT DEFAULT NULL, kick_category VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_3AF346685E237E06 (name), UNIQUE INDEX UNIQ_3AF34668989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE design_names (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_54D238635E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE design_sets (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, design_name_id INT NOT NULL, INDEX IDX_C9AB0174E8BC0CFC (design_name_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE game_category_votes (id INT AUTO_INCREMENT NOT NULL, vote_type SMALLINT NOT NULL, created_at DATETIME NOT NULL, user_uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', game_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_637269CAABFE1C6F (user_uuid), INDEX IDX_637269CAE48FD905 (game_id), INDEX IDX_637269CA12469DE2 (category_id), UNIQUE INDEX unique_user_game_category (user_uuid, game_id, category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE games (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, image LONGTEXT DEFAULT NULL, steam_link VARCHAR(255) DEFAULT NULL, epic_link VARCHAR(255) DEFAULT NULL, gog_link VARCHAR(255) DEFAULT NULL, twitch_category VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, is_category_representative TINYINT DEFAULT 0 NOT NULL, is_active TINYINT DEFAULT 1 NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE game_categories (game_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_D42F8185E48FD905 (game_id), INDEX IDX_D42F818512469DE2 (category_id), PRIMARY KEY (game_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE playthrough_rules (id INT AUTO_INCREMENT NOT NULL, is_active TINYINT NOT NULL, started_at DATETIME DEFAULT NULL, completed_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, playthrough_id INT NOT NULL, rule_id INT NOT NULL, INDEX IDX_3456F21D5F8BD68 (playthrough_id), INDEX IDX_3456F21D744E0351 (rule_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE playthroughs (id INT AUTO_INCREMENT NOT NULL, uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', max_concurrent_rules INT NOT NULL, status VARCHAR(20) NOT NULL, started_at DATETIME DEFAULT NULL, ended_at DATETIME DEFAULT NULL, total_duration INT DEFAULT NULL, created_at DATETIME NOT NULL, user_uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', game_id INT NOT NULL, ruleset_id INT NOT NULL, UNIQUE INDEX UNIQ_DFEEC438D17F50A6 (uuid), INDEX IDX_DFEEC438ABFE1C6F (user_uuid), INDEX IDX_DFEEC438E48FD905 (game_id), INDEX IDX_DFEEC43854F1C144 (ruleset_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE rule_categories (id INT AUTO_INCREMENT NOT NULL, manual_relevance_score SMALLINT DEFAULT NULL, created_at DATETIME NOT NULL, rule_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_142CAB3744E0351 (rule_id), INDEX IDX_142CAB312469DE2 (category_id), UNIQUE INDEX unique_rule_category (rule_id, category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE rule_difficulty_levels (id INT AUTO_INCREMENT NOT NULL, difficulty_level SMALLINT NOT NULL, duration_minutes INT NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, rule_id INT NOT NULL, INDEX IDX_84F9BA34744E0351 (rule_id), UNIQUE INDEX UNIQ_RULE_DIFFICULTY (rule_id, difficulty_level), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE rules (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, rule_type VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE ruleset_rule_cards (id INT AUTO_INCREMENT NOT NULL, position SMALLINT NOT NULL, created_at DATETIME NOT NULL, ruleset_id INT NOT NULL, rule_id INT NOT NULL, tarot_card_identifier VARCHAR(50) NOT NULL, INDEX IDX_B4C5CBAF54F1C144 (ruleset_id), INDEX IDX_B4C5CBAF744E0351 (rule_id), INDEX IDX_B4C5CBAFB11DE030 (tarot_card_identifier), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE ruleset_votes (id INT AUTO_INCREMENT NOT NULL, vote_type SMALLINT NOT NULL, created_at DATETIME NOT NULL, user_uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', ruleset_id INT NOT NULL, INDEX IDX_27462065ABFE1C6F (user_uuid), INDEX IDX_2746206554F1C144 (ruleset_id), UNIQUE INDEX unique_user_ruleset (user_uuid, ruleset_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE rulesets (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, game_id INT NOT NULL, INDEX IDX_AE2A1BD9E48FD905 (game_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE tarot_cards (identifier VARCHAR(50) NOT NULL, display_name VARCHAR(100) NOT NULL, rarity VARCHAR(20) NOT NULL, suit VARCHAR(20) DEFAULT NULL, card_value SMALLINT NOT NULL, sort_order SMALLINT NOT NULL, PRIMARY KEY (identifier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user_favorite_games (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, user_uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', game_id INT NOT NULL, INDEX IDX_222EDF4CABFE1C6F (user_uuid), INDEX IDX_222EDF4CE48FD905 (game_id), UNIQUE INDEX unique_user_game (user_uuid, game_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user_favorite_rulesets (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, user_uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', ruleset_id INT NOT NULL, INDEX IDX_FC62B803ABFE1C6F (user_uuid), INDEX IDX_FC62B80354F1C144 (ruleset_id), UNIQUE INDEX unique_user_ruleset (user_uuid, ruleset_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user_obs_preferences (id INT AUTO_INCREMENT NOT NULL, show_timer_in_setup TINYINT DEFAULT 1 NOT NULL, show_timer_in_active TINYINT DEFAULT 1 NOT NULL, show_timer_in_paused TINYINT DEFAULT 1 NOT NULL, show_timer_in_completed TINYINT DEFAULT 0 NOT NULL, show_status_in_setup TINYINT DEFAULT 1 NOT NULL, show_status_in_active TINYINT DEFAULT 1 NOT NULL, show_status_in_paused TINYINT DEFAULT 1 NOT NULL, show_status_in_completed TINYINT DEFAULT 1 NOT NULL, timer_position VARCHAR(20) DEFAULT \'none\' NOT NULL, timer_design VARCHAR(20) DEFAULT \'numbers\' NOT NULL, status_design VARCHAR(20) DEFAULT \'word\' NOT NULL, rules_design VARCHAR(20) DEFAULT \'list\' NOT NULL, chroma_key_color VARCHAR(9) DEFAULT \'#00FF00\' NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_756D60BCABFE1C6F (user_uuid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE users (uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', email VARCHAR(180) NOT NULL, username VARCHAR(50) NOT NULL, password VARCHAR(255) DEFAULT NULL, oauth_provider VARCHAR(20) DEFAULT NULL, oauth_id VARCHAR(255) DEFAULT NULL, avatar LONGTEXT DEFAULT NULL, discord_id VARCHAR(255) DEFAULT NULL, discord_username VARCHAR(255) DEFAULT NULL, discord_avatar VARCHAR(255) DEFAULT NULL, twitch_id VARCHAR(255) DEFAULT NULL, twitch_username VARCHAR(255) DEFAULT NULL, twitch_avatar VARCHAR(255) DEFAULT NULL, roles JSON NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), UNIQUE INDEX UNIQ_1483A5E9F85E0677 (username), UNIQUE INDEX UNIQ_1483A5E943349DE (discord_id), UNIQUE INDEX UNIQ_1483A5E9DA4E964C (twitch_id), INDEX IDX_1483A5E9_OAUTH (oauth_provider, oauth_id), PRIMARY KEY (uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE card_designs ADD CONSTRAINT FK_6A003A8BB23A9D1B FOREIGN KEY (design_set_id) REFERENCES design_sets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE design_sets ADD CONSTRAINT FK_C9AB0174E8BC0CFC FOREIGN KEY (design_name_id) REFERENCES design_names (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_category_votes ADD CONSTRAINT FK_637269CAABFE1C6F FOREIGN KEY (user_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE game_category_votes ADD CONSTRAINT FK_637269CAE48FD905 FOREIGN KEY (game_id) REFERENCES games (id)');
        $this->addSql('ALTER TABLE game_category_votes ADD CONSTRAINT FK_637269CA12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE game_categories ADD CONSTRAINT FK_D42F8185E48FD905 FOREIGN KEY (game_id) REFERENCES games (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_categories ADD CONSTRAINT FK_D42F818512469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE playthrough_rules ADD CONSTRAINT FK_3456F21D5F8BD68 FOREIGN KEY (playthrough_id) REFERENCES playthroughs (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE playthrough_rules ADD CONSTRAINT FK_3456F21D744E0351 FOREIGN KEY (rule_id) REFERENCES rules (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE playthroughs ADD CONSTRAINT FK_DFEEC438ABFE1C6F FOREIGN KEY (user_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE playthroughs ADD CONSTRAINT FK_DFEEC438E48FD905 FOREIGN KEY (game_id) REFERENCES games (id)');
        $this->addSql('ALTER TABLE playthroughs ADD CONSTRAINT FK_DFEEC43854F1C144 FOREIGN KEY (ruleset_id) REFERENCES rulesets (id)');
        $this->addSql('ALTER TABLE rule_categories ADD CONSTRAINT FK_142CAB3744E0351 FOREIGN KEY (rule_id) REFERENCES rules (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rule_categories ADD CONSTRAINT FK_142CAB312469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rule_difficulty_levels ADD CONSTRAINT FK_84F9BA34744E0351 FOREIGN KEY (rule_id) REFERENCES rules (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ruleset_rule_cards ADD CONSTRAINT FK_B4C5CBAF54F1C144 FOREIGN KEY (ruleset_id) REFERENCES rulesets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ruleset_rule_cards ADD CONSTRAINT FK_B4C5CBAF744E0351 FOREIGN KEY (rule_id) REFERENCES rules (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ruleset_rule_cards ADD CONSTRAINT FK_B4C5CBAFB11DE030 FOREIGN KEY (tarot_card_identifier) REFERENCES tarot_cards (identifier) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ruleset_votes ADD CONSTRAINT FK_27462065ABFE1C6F FOREIGN KEY (user_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE ruleset_votes ADD CONSTRAINT FK_2746206554F1C144 FOREIGN KEY (ruleset_id) REFERENCES rulesets (id)');
        $this->addSql('ALTER TABLE rulesets ADD CONSTRAINT FK_AE2A1BD9E48FD905 FOREIGN KEY (game_id) REFERENCES games (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_favorite_games ADD CONSTRAINT FK_222EDF4CABFE1C6F FOREIGN KEY (user_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE user_favorite_games ADD CONSTRAINT FK_222EDF4CE48FD905 FOREIGN KEY (game_id) REFERENCES games (id)');
        $this->addSql('ALTER TABLE user_favorite_rulesets ADD CONSTRAINT FK_FC62B803ABFE1C6F FOREIGN KEY (user_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE user_favorite_rulesets ADD CONSTRAINT FK_FC62B80354F1C144 FOREIGN KEY (ruleset_id) REFERENCES rulesets (id)');
        $this->addSql('ALTER TABLE user_obs_preferences ADD CONSTRAINT FK_756D60BCABFE1C6F FOREIGN KEY (user_uuid) REFERENCES users (uuid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card_designs DROP FOREIGN KEY FK_6A003A8BB23A9D1B');
        $this->addSql('ALTER TABLE design_sets DROP FOREIGN KEY FK_C9AB0174E8BC0CFC');
        $this->addSql('ALTER TABLE game_category_votes DROP FOREIGN KEY FK_637269CAABFE1C6F');
        $this->addSql('ALTER TABLE game_category_votes DROP FOREIGN KEY FK_637269CAE48FD905');
        $this->addSql('ALTER TABLE game_category_votes DROP FOREIGN KEY FK_637269CA12469DE2');
        $this->addSql('ALTER TABLE game_categories DROP FOREIGN KEY FK_D42F8185E48FD905');
        $this->addSql('ALTER TABLE game_categories DROP FOREIGN KEY FK_D42F818512469DE2');
        $this->addSql('ALTER TABLE playthrough_rules DROP FOREIGN KEY FK_3456F21D5F8BD68');
        $this->addSql('ALTER TABLE playthrough_rules DROP FOREIGN KEY FK_3456F21D744E0351');
        $this->addSql('ALTER TABLE playthroughs DROP FOREIGN KEY FK_DFEEC438ABFE1C6F');
        $this->addSql('ALTER TABLE playthroughs DROP FOREIGN KEY FK_DFEEC438E48FD905');
        $this->addSql('ALTER TABLE playthroughs DROP FOREIGN KEY FK_DFEEC43854F1C144');
        $this->addSql('ALTER TABLE rule_categories DROP FOREIGN KEY FK_142CAB3744E0351');
        $this->addSql('ALTER TABLE rule_categories DROP FOREIGN KEY FK_142CAB312469DE2');
        $this->addSql('ALTER TABLE rule_difficulty_levels DROP FOREIGN KEY FK_84F9BA34744E0351');
        $this->addSql('ALTER TABLE ruleset_rule_cards DROP FOREIGN KEY FK_B4C5CBAF54F1C144');
        $this->addSql('ALTER TABLE ruleset_rule_cards DROP FOREIGN KEY FK_B4C5CBAF744E0351');
        $this->addSql('ALTER TABLE ruleset_rule_cards DROP FOREIGN KEY FK_B4C5CBAFB11DE030');
        $this->addSql('ALTER TABLE ruleset_votes DROP FOREIGN KEY FK_27462065ABFE1C6F');
        $this->addSql('ALTER TABLE ruleset_votes DROP FOREIGN KEY FK_2746206554F1C144');
        $this->addSql('ALTER TABLE rulesets DROP FOREIGN KEY FK_AE2A1BD9E48FD905');
        $this->addSql('ALTER TABLE user_favorite_games DROP FOREIGN KEY FK_222EDF4CABFE1C6F');
        $this->addSql('ALTER TABLE user_favorite_games DROP FOREIGN KEY FK_222EDF4CE48FD905');
        $this->addSql('ALTER TABLE user_favorite_rulesets DROP FOREIGN KEY FK_FC62B803ABFE1C6F');
        $this->addSql('ALTER TABLE user_favorite_rulesets DROP FOREIGN KEY FK_FC62B80354F1C144');
        $this->addSql('ALTER TABLE user_obs_preferences DROP FOREIGN KEY FK_756D60BCABFE1C6F');
        $this->addSql('DROP TABLE card_designs');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE design_names');
        $this->addSql('DROP TABLE design_sets');
        $this->addSql('DROP TABLE game_category_votes');
        $this->addSql('DROP TABLE games');
        $this->addSql('DROP TABLE game_categories');
        $this->addSql('DROP TABLE playthrough_rules');
        $this->addSql('DROP TABLE playthroughs');
        $this->addSql('DROP TABLE rule_categories');
        $this->addSql('DROP TABLE rule_difficulty_levels');
        $this->addSql('DROP TABLE rules');
        $this->addSql('DROP TABLE ruleset_rule_cards');
        $this->addSql('DROP TABLE ruleset_votes');
        $this->addSql('DROP TABLE rulesets');
        $this->addSql('DROP TABLE tarot_cards');
        $this->addSql('DROP TABLE user_favorite_games');
        $this->addSql('DROP TABLE user_favorite_rulesets');
        $this->addSql('DROP TABLE user_obs_preferences');
        $this->addSql('DROP TABLE users');
    }
}
