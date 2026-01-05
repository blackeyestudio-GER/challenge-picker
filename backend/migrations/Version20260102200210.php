<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260102200210 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE playthrough_rule_queue (id INT AUTO_INCREMENT NOT NULL, difficulty_level INT NOT NULL, position INT NOT NULL, queued_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', queued_by_user_uuid BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', status VARCHAR(20) NOT NULL, processed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', failure_reason LONGTEXT DEFAULT NULL, playthrough_id INT NOT NULL, rule_id INT NOT NULL, INDEX IDX_689796C45F8BD68 (playthrough_id), INDEX IDX_689796C4744E0351 (rule_id), INDEX idx_playthrough_status (playthrough_id, queued_by_user_uuid, status), INDEX idx_status_position (status, position), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE playthrough_rule_queue ADD CONSTRAINT FK_689796C45F8BD68 FOREIGN KEY (playthrough_id) REFERENCES playthroughs (id)');
        $this->addSql('ALTER TABLE playthrough_rule_queue ADD CONSTRAINT FK_689796C4744E0351 FOREIGN KEY (rule_id) REFERENCES rules (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE playthrough_rule_queue DROP FOREIGN KEY FK_689796C45F8BD68');
        $this->addSql('ALTER TABLE playthrough_rule_queue DROP FOREIGN KEY FK_689796C4744E0351');
        $this->addSql('DROP TABLE playthrough_rule_queue');
    }
}
