<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fix DateTimeImmutable for expires_at column (missed in previous migration)
 */
final class Version20260102190100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add COMMENT to expires_at column in playthrough_rules to fix DateTimeImmutable conversion';
    }

    public function up(Schema $schema): void
    {
        // Add Doctrine type comment to expires_at column
        $this->addSql('ALTER TABLE playthrough_rules MODIFY expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // Remove type comment
        $this->addSql('ALTER TABLE playthrough_rules MODIFY expires_at DATETIME DEFAULT NULL');
    }
}

