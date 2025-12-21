<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Associate category representative games with their respective categories
 */
final class Version20251222000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add category associations for category representative games (FPS, Soulslikes, etc.)';
    }

    public function up(Schema $schema): void
    {
        // Get or create system user for votes
        $this->addSql("
            INSERT INTO users (uuid, email, username, password, roles, created_at, updated_at) 
            SELECT UUID(), 'system@challenge-picker.local', 'System', '$2y$13\$placeholder.hash.for.system.user', '[]', NOW(), NOW()
            WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'system@challenge-picker.local')
        ");
        $this->addSql("SET @system_user = (SELECT uuid FROM users WHERE email = 'system@challenge-picker.local')");

        // FPS game -> FPS category
        $this->addSql("
            INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at)
            SELECT g.id, c.id, @system_user, NOW()
            FROM games g
            JOIN categories c ON c.slug = 'fps'
            WHERE g.name = 'FPS' AND g.is_category_representative = 1
        ");

        // Soulslikes game -> Soulslikes category
        $this->addSql("
            INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at)
            SELECT g.id, c.id, @system_user, NOW()
            FROM games g
            JOIN categories c ON c.slug = 'soulslikes'
            WHERE g.name = 'Soulslikes' AND g.is_category_representative = 1
        ");

        // 3rd Person Shooter game -> 3rd Person Shooter category
        $this->addSql("
            INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at)
            SELECT g.id, c.id, @system_user, NOW()
            FROM games g
            JOIN categories c ON c.slug = '3rd-person-shooter'
            WHERE g.name = '3rd Person Shooter' AND g.is_category_representative = 1
        ");

        // Horror game -> Horror category
        $this->addSql("
            INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at)
            SELECT g.id, c.id, @system_user, NOW()
            FROM games g
            JOIN categories c ON c.slug = 'horror'
            WHERE g.name = 'Horror' AND g.is_category_representative = 1
        ");

        // Zombies game -> Zombies category
        $this->addSql("
            INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at)
            SELECT g.id, c.id, @system_user, NOW()
            FROM games g
            JOIN categories c ON c.slug = 'zombies'
            WHERE g.name = 'Zombies' AND g.is_category_representative = 1
        ");

        // Aliens game -> Aliens category
        $this->addSql("
            INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at)
            SELECT g.id, c.id, @system_user, NOW()
            FROM games g
            JOIN categories c ON c.slug = 'aliens'
            WHERE g.name = 'Aliens' AND g.is_category_representative = 1
        ");

        // Monsters game -> Monsters category
        $this->addSql("
            INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at)
            SELECT g.id, c.id, @system_user, NOW()
            FROM games g
            JOIN categories c ON c.slug = 'monsters'
            WHERE g.name = 'Monsters' AND g.is_category_representative = 1
        ");
    }

    public function down(Schema $schema): void
    {
        // Remove category associations for representative games
        $this->addSql("
            DELETE gcv FROM game_category_votes gcv
            JOIN games g ON gcv.game_id = g.id
            WHERE g.is_category_representative = 1
        ");
    }
}

