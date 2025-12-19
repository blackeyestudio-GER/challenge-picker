<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Make UUID the primary key for users table
 */
final class Version20251219170100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make UUID the primary key for users table';
    }

    public function up(Schema $schema): void
    {
        // Make UUID the primary key on users
        // First, remove AUTO_INCREMENT from id column
        $this->addSql('ALTER TABLE users MODIFY id INT NOT NULL');
        // Drop the old primary key
        $this->addSql('ALTER TABLE users DROP PRIMARY KEY');
        // Drop the integer id column
        $this->addSql('ALTER TABLE users DROP id');
        // Add primary key on UUID
        $this->addSql('ALTER TABLE users ADD PRIMARY KEY (uuid)');
    }

    public function down(Schema $schema): void
    {
        // Reverting this would be complex, so we'll leave it as-is
        $this->addSql('ALTER TABLE users ADD id INT AUTO_INCREMENT NOT NULL FIRST, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9D17F50A6 ON users (uuid)');
    }
}

