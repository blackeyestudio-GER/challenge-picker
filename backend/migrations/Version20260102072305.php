<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260102072305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove sort_order from design_sets - shop will handle ordering';
    }

    public function up(Schema $schema): void
    {
        // Remove sort_order column from design_sets
        $this->addSql('ALTER TABLE design_sets DROP sort_order');
    }

    public function down(Schema $schema): void
    {
        // Restore sort_order column
        $this->addSql('ALTER TABLE design_sets ADD sort_order SMALLINT NOT NULL DEFAULT 0');
    }
}
