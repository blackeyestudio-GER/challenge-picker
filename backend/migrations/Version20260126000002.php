<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add isAutomated field to payout_requests table
 */
final class Version20260126000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add isAutomated field to payout_requests table for automated cron job payouts';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE payout_requests ADD is_automated TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE payout_requests DROP is_automated');
    }
}
