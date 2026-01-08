<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260106000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add resulting_playthrough columns to challenges table';
    }

    public function up(Schema $schema): void
    {
        // Add resulting_playthrough_id column
        $this->addSql('ALTER TABLE challenges ADD resulting_playthrough_id INT DEFAULT NULL');
        
        // Add resulting_playthrough_user_uuid column (BINARY(16) to match UUID type)
        $this->addSql('ALTER TABLE challenges ADD resulting_playthrough_user_uuid BINARY(16) DEFAULT NULL');
        
        // Add foreign key constraints
        $this->addSql('ALTER TABLE challenges ADD CONSTRAINT FK_challenges_resulting_playthrough FOREIGN KEY (resulting_playthrough_id, resulting_playthrough_user_uuid) REFERENCES playthroughs (id, user_uuid) ON DELETE SET NULL');
        
        // Add index for better query performance
        $this->addSql('CREATE INDEX IDX_challenges_resulting_playthrough ON challenges (resulting_playthrough_id, resulting_playthrough_user_uuid)');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key constraint
        $this->addSql('ALTER TABLE challenges DROP FOREIGN KEY FK_challenges_resulting_playthrough');
        
        // Drop index
        $this->addSql('DROP INDEX IDX_challenges_resulting_playthrough ON challenges');
        
        // Drop columns
        $this->addSql('ALTER TABLE challenges DROP resulting_playthrough_id');
        $this->addSql('ALTER TABLE challenges DROP resulting_playthrough_user_uuid');
    }
}

