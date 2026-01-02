<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260102065502 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE challenges (uuid BINARY(16) NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', responded_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', challenger_uuid BINARY(16) NOT NULL, challenged_user_uuid BINARY(16) NOT NULL, playthrough_id INT NOT NULL, playthrough_user_uuid BINARY(16) NOT NULL, INDEX IDX_7B5A7E033BABF47 (challenger_uuid), INDEX IDX_7B5A7E05F8BD68256DA786 (playthrough_id, playthrough_user_uuid), INDEX idx_challenged_user (challenged_user_uuid), INDEX idx_status (status), PRIMARY KEY (uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE challenges ADD CONSTRAINT FK_7B5A7E033BABF47 FOREIGN KEY (challenger_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE challenges ADD CONSTRAINT FK_7B5A7E08D396823 FOREIGN KEY (challenged_user_uuid) REFERENCES users (uuid)');
        $this->addSql('ALTER TABLE challenges ADD CONSTRAINT FK_7B5A7E05F8BD68256DA786 FOREIGN KEY (playthrough_id, playthrough_user_uuid) REFERENCES playthroughs (id, user_uuid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE challenges DROP FOREIGN KEY FK_7B5A7E033BABF47');
        $this->addSql('ALTER TABLE challenges DROP FOREIGN KEY FK_7B5A7E08D396823');
        $this->addSql('ALTER TABLE challenges DROP FOREIGN KEY FK_7B5A7E05F8BD68256DA786');
        $this->addSql('DROP TABLE challenges');
    }
}
