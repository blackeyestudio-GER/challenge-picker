<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251219164947 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add UUID field to users table for persistent OBS overlay URLs';
    }

    public function up(Schema $schema): void
    {
        // Add uuid column as nullable first
        $this->addSql('ALTER TABLE users ADD uuid VARCHAR(36) DEFAULT NULL');
        
        // Generate UUIDs for existing users
        $this->addSql('UPDATE users SET uuid = UUID() WHERE uuid IS NULL');
        
        // Make column NOT NULL and add unique index
        $this->addSql('ALTER TABLE users MODIFY uuid VARCHAR(36) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9D17F50A6 ON users (uuid)');
        
        // Update user_obs_preferences default
        $this->addSql('ALTER TABLE user_obs_preferences CHANGE show_timer_in_setup show_timer_in_setup TINYINT DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_obs_preferences CHANGE show_timer_in_setup show_timer_in_setup TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('DROP INDEX UNIQ_1483A5E9D17F50A6 ON users');
        $this->addSql('ALTER TABLE users DROP uuid');
    }
}
