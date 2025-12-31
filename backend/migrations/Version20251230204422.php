<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251230204422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card_designs ADD is_template TINYINT NOT NULL, ADD template_type VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE design_sets ADD type VARCHAR(20) NOT NULL, ADD is_premium TINYINT NOT NULL, ADD price NUMERIC(10, 2) DEFAULT NULL, ADD theme VARCHAR(50) DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, ADD sort_order SMALLINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card_designs DROP is_template, DROP template_type');
        $this->addSql('ALTER TABLE design_sets DROP type, DROP is_premium, DROP price, DROP theme, DROP description, DROP sort_order');
    }
}
