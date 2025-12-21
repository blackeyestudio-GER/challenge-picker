<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221231935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user_favorite_games table for favorites feature';
    }

    public function up(Schema $schema): void
    {
        // Create user_favorite_games table
        $this->addSql('CREATE TABLE user_favorite_games (id INT AUTO_INCREMENT NOT NULL, user_uuid VARCHAR(36) NOT NULL, game_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_222EDF4CABFE1C6F (user_uuid), INDEX IDX_222EDF4CE48FD905 (game_id), UNIQUE INDEX unique_user_game (user_uuid, game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_favorite_games ADD CONSTRAINT FK_222EDF4CABFE1C6F FOREIGN KEY (user_uuid) REFERENCES users (uuid) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_favorite_games ADD CONSTRAINT FK_222EDF4CE48FD905 FOREIGN KEY (game_id) REFERENCES games (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_favorite_games DROP FOREIGN KEY FK_222EDF4CABFE1C6F');
        $this->addSql('ALTER TABLE user_favorite_games DROP FOREIGN KEY FK_222EDF4CE48FD905');
        $this->addSql('DROP TABLE user_favorite_games');
    }
}
