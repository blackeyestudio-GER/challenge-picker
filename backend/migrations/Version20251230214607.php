<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251230214607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE shop_transactions (id INT AUTO_INCREMENT NOT NULL, user_uuid BINARY(16) NOT NULL, stripe_session_id VARCHAR(255) NOT NULL, stripe_payment_intent_id VARCHAR(255) DEFAULT NULL, status VARCHAR(20) NOT NULL, amount NUMERIC(10, 2) NOT NULL, currency VARCHAR(3) NOT NULL, items JSON NOT NULL, created_at DATETIME NOT NULL, completed_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_F7CD7DDE1A314A57 (stripe_session_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user_design_sets (id INT AUTO_INCREMENT NOT NULL, user_uuid BINARY(16) NOT NULL, purchased_at DATETIME NOT NULL, price_paid NUMERIC(10, 2) NOT NULL, currency VARCHAR(3) DEFAULT NULL, design_set_id INT NOT NULL, INDEX IDX_60B0C8E1B23A9D1B (design_set_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user_design_sets ADD CONSTRAINT FK_60B0C8E1B23A9D1B FOREIGN KEY (design_set_id) REFERENCES design_sets (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_design_sets DROP FOREIGN KEY FK_60B0C8E1B23A9D1B');
        $this->addSql('DROP TABLE shop_transactions');
        $this->addSql('DROP TABLE user_design_sets');
    }
}
