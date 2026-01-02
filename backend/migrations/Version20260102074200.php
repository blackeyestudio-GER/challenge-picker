<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add active_design_set_id to users table for user design preferences
 */
final class Version20260102074200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add active_design_set_id to users table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD active_design_set_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP active_design_set_id');
    }
}

