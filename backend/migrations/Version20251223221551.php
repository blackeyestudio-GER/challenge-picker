<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251223221551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed survival horror style rules (Basic and Court variants)';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');

        // ==================== WALKING ONLY ====================
        // Basic: Walking Only (9 levels, 60s increments)
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES (77, 'Walking Only', 'Movement restricted to walking speed only, no running allowed', 'basic', '$now')");
        $walkingBasicId = 77;
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($walkingBasicId, $level, $duration, '$now')");
        }

        // Court: Walking Only (4 levels, 300s increments)
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES (78, 'Walking Only', 'Movement restricted to walking speed only, no running allowed', 'court', '$now')");
        $walkingCourtId = 78;
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($walkingCourtId, $level, $duration, '$now')");
        }

        // ==================== NO FLASHLIGHT ====================
        // Basic: No Flashlight (9 levels, 60s increments)
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES (79, 'No Flashlight', 'Navigate in darkness without using flashlight or any light sources', 'basic', '$now')");
        $flashlightBasicId = 79;
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($flashlightBasicId, $level, $duration, '$now')");
        }

        // Court: No Flashlight (4 levels, 300s increments)
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES (80, 'No Flashlight', 'Navigate in darkness without using flashlight or any light sources', 'court', '$now')");
        $flashlightCourtId = 80;
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($flashlightCourtId, $level, $duration, '$now')");
        }

        // ==================== NO MAP ====================
        // Basic: No Map (9 levels, 60s increments)
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES (81, 'No Map', 'Navigate without accessing the map or minimap', 'basic', '$now')");
        $mapBasicId = 81;
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($mapBasicId, $level, $duration, '$now')");
        }

        // Court: No Map (4 levels, 300s increments)
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES (82, 'No Map', 'Navigate without accessing the map or minimap', 'court', '$now')");
        $mapCourtId = 82;
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($mapCourtId, $level, $duration, '$now')");
        }

        // ==================== NO HIDING ====================
        // Basic: No Hiding (9 levels, 60s increments)
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES (83, 'No Hiding', 'Cannot use lockers, cabinets, or any hiding spots', 'basic', '$now')");
        $hidingBasicId = 83;
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($hidingBasicId, $level, $duration, '$now')");
        }

        // Court: No Hiding (4 levels, 300s increments)
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES (84, 'No Hiding', 'Cannot use lockers, cabinets, or any hiding spots', 'court', '$now')");
        $hidingCourtId = 84;
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($hidingCourtId, $level, $duration, '$now')");
        }

        // ==================== NO SAFE ROOMS ====================
        // Basic: No Safe Rooms (9 levels, 60s increments)
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES (85, 'No Safe Rooms', 'Cannot enter safe rooms or designated safe zones', 'basic', '$now')");
        $safeRoomBasicId = 85;
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($safeRoomBasicId, $level, $duration, '$now')");
        }

        // Court: No Safe Rooms (4 levels, 300s increments)
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES (86, 'No Safe Rooms', 'Cannot enter safe rooms or designated safe zones', 'court', '$now')");
        $safeRoomCourtId = 86;
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($safeRoomCourtId, $level, $duration, '$now')");
        }
    }

    public function down(Schema $schema): void
    {
        // Delete all rules and their difficulty levels (cascade will handle difficulty levels)
        $this->addSql('DELETE FROM rules WHERE id BETWEEN 77 AND 86');
    }
}
