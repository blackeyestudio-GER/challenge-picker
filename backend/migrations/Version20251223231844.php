<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add Discord and Twitch OAuth connection fields to users table
 */
final class Version20251223231844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Discord and Twitch OAuth connection fields to users table';
    }

    public function up(Schema $schema): void
    {
        // Add Discord fields
        $this->addSql('ALTER TABLE users ADD discord_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD discord_username VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD discord_avatar VARCHAR(255) DEFAULT NULL');
        
        // Add Twitch fields
        $this->addSql('ALTER TABLE users ADD twitch_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD twitch_username VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD twitch_avatar VARCHAR(255) DEFAULT NULL');
        
        // Add unique indexes for OAuth IDs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DISCORD_ID ON users (discord_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_TWITCH_ID ON users (twitch_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_DISCORD_ID ON users');
        $this->addSql('DROP INDEX UNIQ_TWITCH_ID ON users');
        $this->addSql('ALTER TABLE users DROP discord_id, DROP discord_username, DROP discord_avatar');
        $this->addSql('ALTER TABLE users DROP twitch_id, DROP twitch_username, DROP twitch_avatar');
    }
}
