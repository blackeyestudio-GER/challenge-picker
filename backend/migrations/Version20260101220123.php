<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260101220123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Update playthroughs table to have composite primary key (id, user_uuid)
        $this->addSql('ALTER TABLE playthroughs DROP PRIMARY KEY, ADD PRIMARY KEY (id, user_uuid)');
        
        // Add playthrough_user_uuid column to playthrough_rules (check if exists first via information_schema)
        // MySQL doesn't support IF NOT EXISTS for ADD COLUMN, so we check manually
        $this->addSql("
            SET @col_exists = (
                SELECT COUNT(*) 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'playthrough_rules' 
                AND COLUMN_NAME = 'playthrough_user_uuid'
            );
            SET @sql = IF(@col_exists = 0, 
                'ALTER TABLE playthrough_rules ADD playthrough_user_uuid BINARY(16) NOT NULL AFTER playthrough_id',
                'SELECT 1'
            );
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        ");
        
        // Populate playthrough_user_uuid from playthroughs table
        $this->addSql('UPDATE playthrough_rules pr JOIN playthroughs p ON pr.playthrough_id = p.id SET pr.playthrough_user_uuid = p.user_uuid WHERE pr.playthrough_user_uuid IS NULL OR pr.playthrough_user_uuid = 0x00000000000000000000000000000000');
        
        // Drop old single-column foreign key constraint (if it exists)
        // Check if constraint exists before dropping
        $this->addSql("
            SET @fk_exists = (
                SELECT COUNT(*) 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'playthrough_rules' 
                AND CONSTRAINT_NAME = 'FK_3456F21D5F8BD68'
                AND REFERENCED_COLUMN_NAME = 'id'
                AND COLUMN_NAME = 'playthrough_id'
            );
            SET @sql = IF(@fk_exists > 0, 
                'ALTER TABLE playthrough_rules DROP FOREIGN KEY FK_3456F21D5F8BD68',
                'SELECT 1'
            );
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        ");
        
        // Drop old index if it exists
        $this->addSql("
            SET @idx_exists = (
                SELECT COUNT(*) 
                FROM INFORMATION_SCHEMA.STATISTICS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'playthrough_rules' 
                AND INDEX_NAME = 'IDX_3456F21D5F8BD68'
                AND SEQ_IN_INDEX = 1
            );
            SET @sql = IF(@idx_exists > 0, 
                'DROP INDEX IDX_3456F21D5F8BD68 ON playthrough_rules',
                'SELECT 1'
            );
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        ");
        
        // Add composite foreign key constraint
        $this->addSql('ALTER TABLE playthrough_rules ADD CONSTRAINT FK_3456F21D5F8BD68 FOREIGN KEY (playthrough_id, playthrough_user_uuid) REFERENCES playthroughs (id, user_uuid) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_3456F21D5F8BD68 ON playthrough_rules (playthrough_id, playthrough_user_uuid)');
    }

    public function down(Schema $schema): void
    {
        // Drop composite foreign key constraint
        $this->addSql('ALTER TABLE playthrough_rules DROP FOREIGN KEY `FK_3456F21D5F8BD68`');
        $this->addSql('DROP INDEX IDX_3456F21D5F8BD68 ON playthrough_rules');
        
        // Remove playthrough_user_uuid column
        $this->addSql('ALTER TABLE playthrough_rules DROP playthrough_user_uuid');
        
        // Recreate single-column foreign key
        $this->addSql('ALTER TABLE playthrough_rules ADD CONSTRAINT FK_3456F21D5F8BD68 FOREIGN KEY (playthrough_id) REFERENCES playthroughs (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_3456F21D5F8BD68 ON playthrough_rules (playthrough_id)');
        
        // Revert playthroughs to single-column primary key
        $this->addSql('ALTER TABLE playthroughs DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE playthroughs CHANGE id id INT AUTO_INCREMENT NOT NULL');
    }
}
