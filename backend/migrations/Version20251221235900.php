<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add popular games with category associations
 */
final class Version20251221235900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add 15 popular games and their category associations';
    }

    public function up(Schema $schema): void
    {
        // Get category IDs
        $this->addSql("SET @fps_id = (SELECT id FROM categories WHERE slug = 'fps')");
        $this->addSql("SET @soulslikes_id = (SELECT id FROM categories WHERE slug = 'soulslikes')");
        $this->addSql("SET @third_person_id = (SELECT id FROM categories WHERE slug = '3rd-person-shooter')");
        $this->addSql("SET @horror_id = (SELECT id FROM categories WHERE slug = 'horror')");
        $this->addSql("SET @zombies_id = (SELECT id FROM categories WHERE slug = 'zombies')");
        $this->addSql("SET @aliens_id = (SELECT id FROM categories WHERE slug = 'aliens')");
        $this->addSql("SET @monsters_id = (SELECT id FROM categories WHERE slug = 'monsters')");

        // Insert games
        $this->addSql("INSERT INTO games (name, description, created_at, is_category_representative) VALUES 
            ('Elden Ring', 'An action RPG set in a vast open world, featuring challenging combat and deep lore from FromSoftware.', NOW(), 0),
            ('Call of Duty: Modern Warfare', 'A first-person shooter with intense multiplayer action and a gripping campaign.', NOW(), 0),
            ('Resident Evil 4', 'Survival horror masterpiece where Leon Kennedy fights parasitic zombies in a Spanish village.', NOW(), 0),
            ('Counter-Strike 2', 'The legendary competitive tactical shooter, completely rebuilt with Source 2 engine.', NOW(), 0),
            ('Dark Souls III', 'The epic dark fantasy action RPG with brutal combat and interconnected world design.', NOW(), 0),
            ('Left 4 Dead 2', 'Cooperative zombie shooter where you fight through hordes of infected with friends.', NOW(), 0),
            ('Dead Space', 'Sci-fi horror masterpiece set on a space station infested with necromorphs.', NOW(), 0),
            ('DOOM Eternal', 'Fast-paced demon slaying FPS with intense combat and heavy metal soundtrack.', NOW(), 0),
            ('The Last of Us', 'Post-apocalyptic survival game following Joel and Ellie through a fungal zombie outbreak.', NOW(), 0),
            ('Alien: Isolation', 'Survival horror where you evade a single, terrifyingly intelligent xenomorph.', NOW(), 0),
            ('Bloodborne', 'Gothic horror action RPG from FromSoftware set in the cursed city of Yharnam.', NOW(), 0),
            ('Dying Light 2', 'First-person parkour zombie survival with dynamic day-night cycles.', NOW(), 0),
            ('Halo Infinite', 'Master Chief returns to fight the Banished in this sci-fi FPS epic.', NOW(), 0),
            ('Gears of War', 'Third-person cover shooter fighting alien Locust hordes on planet Sera.', NOW(), 0),
            ('Monster Hunter: World', 'Hunt massive monsters in this action-packed cooperative RPG.', NOW(), 0)
        ");

        // Create a dummy user for system votes (this ensures votes appear community-driven)
        // In production, you might want actual user votes or mark these as "system votes"
        $this->addSql("
            INSERT INTO users (uuid, email, username, password, roles, created_at, updated_at) 
            SELECT UUID(), 'system@challenge-picker.local', 'System', '$2y$13\$placeholder.hash.for.system.user', '[]', NOW(), NOW()
            WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'system@challenge-picker.local')
        ");
        $this->addSql("SET @system_user = (SELECT uuid FROM users WHERE email = 'system@challenge-picker.local')");

        // Associate games with categories via votes
        // Elden Ring - Soulslikes, Monsters
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @soulslikes_id, @system_user, NOW() FROM games g WHERE g.name = 'Elden Ring'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @monsters_id, @system_user, NOW() FROM games g WHERE g.name = 'Elden Ring'");

        // Call of Duty: Modern Warfare - FPS
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @fps_id, @system_user, NOW() FROM games g WHERE g.name = 'Call of Duty: Modern Warfare'");

        // Resident Evil 4 - Horror, 3rd Person Shooter, Zombies
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @horror_id, @system_user, NOW() FROM games g WHERE g.name = 'Resident Evil 4'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @third_person_id, @system_user, NOW() FROM games g WHERE g.name = 'Resident Evil 4'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @zombies_id, @system_user, NOW() FROM games g WHERE g.name = 'Resident Evil 4'");

        // Counter-Strike 2 - FPS
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @fps_id, @system_user, NOW() FROM games g WHERE g.name = 'Counter-Strike 2'");

        // Dark Souls III - Soulslikes, Monsters
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @soulslikes_id, @system_user, NOW() FROM games g WHERE g.name = 'Dark Souls III'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @monsters_id, @system_user, NOW() FROM games g WHERE g.name = 'Dark Souls III'");

        // Left 4 Dead 2 - FPS, Zombies, Horror
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @fps_id, @system_user, NOW() FROM games g WHERE g.name = 'Left 4 Dead 2'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @zombies_id, @system_user, NOW() FROM games g WHERE g.name = 'Left 4 Dead 2'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @horror_id, @system_user, NOW() FROM games g WHERE g.name = 'Left 4 Dead 2'");

        // Dead Space - Horror, Aliens, 3rd Person Shooter, Monsters
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @horror_id, @system_user, NOW() FROM games g WHERE g.name = 'Dead Space'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @aliens_id, @system_user, NOW() FROM games g WHERE g.name = 'Dead Space'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @third_person_id, @system_user, NOW() FROM games g WHERE g.name = 'Dead Space'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @monsters_id, @system_user, NOW() FROM games g WHERE g.name = 'Dead Space'");

        // DOOM Eternal - FPS, Monsters
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @fps_id, @system_user, NOW() FROM games g WHERE g.name = 'DOOM Eternal'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @monsters_id, @system_user, NOW() FROM games g WHERE g.name = 'DOOM Eternal'");

        // The Last of Us - Zombies, Horror, 3rd Person Shooter
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @zombies_id, @system_user, NOW() FROM games g WHERE g.name = 'The Last of Us'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @horror_id, @system_user, NOW() FROM games g WHERE g.name = 'The Last of Us'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @third_person_id, @system_user, NOW() FROM games g WHERE g.name = 'The Last of Us'");

        // Alien: Isolation - Horror, Aliens
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @horror_id, @system_user, NOW() FROM games g WHERE g.name = 'Alien: Isolation'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @aliens_id, @system_user, NOW() FROM games g WHERE g.name = 'Alien: Isolation'");

        // Bloodborne - Soulslikes, Horror, Monsters
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @soulslikes_id, @system_user, NOW() FROM games g WHERE g.name = 'Bloodborne'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @horror_id, @system_user, NOW() FROM games g WHERE g.name = 'Bloodborne'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @monsters_id, @system_user, NOW() FROM games g WHERE g.name = 'Bloodborne'");

        // Dying Light 2 - FPS, Zombies, Horror
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @fps_id, @system_user, NOW() FROM games g WHERE g.name = 'Dying Light 2'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @zombies_id, @system_user, NOW() FROM games g WHERE g.name = 'Dying Light 2'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @horror_id, @system_user, NOW() FROM games g WHERE g.name = 'Dying Light 2'");

        // Halo Infinite - FPS, Aliens
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @fps_id, @system_user, NOW() FROM games g WHERE g.name = 'Halo Infinite'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @aliens_id, @system_user, NOW() FROM games g WHERE g.name = 'Halo Infinite'");

        // Gears of War - 3rd Person Shooter, Aliens, Monsters
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @third_person_id, @system_user, NOW() FROM games g WHERE g.name = 'Gears of War'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @aliens_id, @system_user, NOW() FROM games g WHERE g.name = 'Gears of War'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @monsters_id, @system_user, NOW() FROM games g WHERE g.name = 'Gears of War'");

        // Monster Hunter: World - Monsters, 3rd Person Shooter
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @monsters_id, @system_user, NOW() FROM games g WHERE g.name = 'Monster Hunter: World'");
        $this->addSql("INSERT INTO game_category_votes (game_id, category_id, user_uuid, created_at) 
            SELECT g.id, @third_person_id, @system_user, NOW() FROM games g WHERE g.name = 'Monster Hunter: World'");
    }

    public function down(Schema $schema): void
    {
        // Remove game category votes for these games
        $this->addSql("DELETE FROM game_category_votes WHERE game_id IN (
            SELECT id FROM games WHERE name IN (
                'Elden Ring', 'Call of Duty: Modern Warfare', 'Resident Evil 4', 
                'Counter-Strike 2', 'Dark Souls III', 'Left 4 Dead 2', 'Dead Space',
                'DOOM Eternal', 'The Last of Us', 'Alien: Isolation', 'Bloodborne',
                'Dying Light 2', 'Halo Infinite', 'Gears of War', 'Monster Hunter: World'
            )
        )");

        // Remove the games
        $this->addSql("DELETE FROM games WHERE name IN (
            'Elden Ring', 'Call of Duty: Modern Warfare', 'Resident Evil 4', 
            'Counter-Strike 2', 'Dark Souls III', 'Left 4 Dead 2', 'Dead Space',
            'DOOM Eternal', 'The Last of Us', 'Alien: Isolation', 'Bloodborne',
            'Dying Light 2', 'Halo Infinite', 'Gears of War', 'Monster Hunter: World'
        )");

        // Optionally remove system user if no other data depends on it
        $this->addSql("DELETE FROM users WHERE email = 'system@challenge-picker.local'");
    }
}

