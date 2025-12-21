<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221215733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create game_category_votes table for community-driven game categorization system';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE game_category_votes (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, user_uuid VARCHAR(36) NOT NULL, game_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_637269CAABFE1C6F (user_uuid), INDEX IDX_637269CAE48FD905 (game_id), INDEX IDX_637269CA12469DE2 (category_id), UNIQUE INDEX unique_user_game_category (user_uuid, game_id, category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE game_category_votes ADD CONSTRAINT FK_637269CAABFE1C6F FOREIGN KEY (user_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE game_category_votes ADD CONSTRAINT FK_637269CAE48FD905 FOREIGN KEY (game_id) REFERENCES games (id)');
        $this->addSql('ALTER TABLE game_category_votes ADD CONSTRAINT FK_637269CA12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE categories CHANGE created_at created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_category_votes DROP FOREIGN KEY FK_637269CAABFE1C6F');
        $this->addSql('ALTER TABLE game_category_votes DROP FOREIGN KEY FK_637269CAE48FD905');
        $this->addSql('ALTER TABLE game_category_votes DROP FOREIGN KEY FK_637269CA12469DE2');
        $this->addSql('DROP TABLE game_category_votes');
        $this->addSql('ALTER TABLE categories CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
