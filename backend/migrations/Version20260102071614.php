<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260102071614 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Add is_free column to design_sets (defaults to true for free items)
        $this->addSql('ALTER TABLE design_sets ADD is_free TINYINT(1) NOT NULL DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        // Remove is_free column from design_sets
        $this->addSql('ALTER TABLE design_sets DROP is_free');
    }
}
