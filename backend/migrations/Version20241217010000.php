<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Change games.image from VARCHAR(500) to TEXT for base64 image storage
 */
final class Version20241217010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change games.image to TEXT for base64 image storage';
    }

    public function up(Schema $schema): void
    {
        // Change image column from VARCHAR(500) to TEXT for base64 storage
        $this->addSql('ALTER TABLE games MODIFY image TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Revert back to VARCHAR(500)
        $this->addSql('ALTER TABLE games MODIFY image VARCHAR(500) DEFAULT NULL');
    }
}

