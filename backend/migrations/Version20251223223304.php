<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Seed comprehensive game challenge rules (Basic and Court variants)
 */
final class Version20251223223304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed comprehensive challenge rules covering movement, combat, stealth, abilities, and UI';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');
        $ruleId = 87;

        // ==================== MOVEMENT & TRAVERSAL RULES ====================
        
        // No Double Jump
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Double Jump', 'Double jump and air dash abilities are disabled', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Double Jump', 'Double jump and air dash abilities are disabled', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // No Wall Jump
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Wall Jump', 'Wall climbing and wall jumping mechanics are disabled', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Wall Jump', 'Wall climbing and wall jumping mechanics are disabled', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // No Climbing
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Climbing', 'Cannot climb ladders, walls, or ledges', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Climbing', 'Cannot climb ladders, walls, or ledges', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // Backwards Only
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'Backwards Only', 'Can only move backwards through the level', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'Backwards Only', 'Can only move backwards through the level', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // No Fast Travel
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Fast Travel', 'Fast travel is disabled, must traverse the world manually', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Fast Travel', 'Fast travel is disabled, must traverse the world manually', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // ==================== STEALTH & COMBAT STYLE RULES ====================

        // No Killing
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Killing', 'Pacifist run - cannot kill any enemies, knockouts and avoidance only', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Killing', 'Pacifist run - cannot kill any enemies, knockouts and avoidance only', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // Stealth Only
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'Stealth Only', 'Cannot be detected by enemies, stealth approach required', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'Stealth Only', 'Cannot be detected by enemies, stealth approach required', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // No Stealth Allowed
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Stealth Allowed', 'Stealth mechanics are disabled, combat only approach', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Stealth Allowed', 'Stealth mechanics are disabled, combat only approach', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // Melee Only
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'Melee Only', 'Can only use melee weapons, all ranged weapons disabled', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'Melee Only', 'Can only use melee weapons, all ranged weapons disabled', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // Ranged Only
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'Ranged Only', 'Can only use ranged weapons, all melee attacks disabled', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'Ranged Only', 'Can only use ranged weapons, all melee attacks disabled', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // ==================== RESOURCE MANAGEMENT RULES ====================

        // No Looting
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Looting', 'Cannot loot items from containers, corpses, or the environment', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Looting', 'Cannot loot items from containers, corpses, or the environment', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // No Resource Gathering
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Resource Gathering', 'Cannot gather or harvest resources from the environment', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Resource Gathering', 'Cannot gather or harvest resources from the environment', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // ==================== COMBAT MECHANICS RULES ====================

        // No Blocking
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Blocking', 'Cannot use block or shield mechanics', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Blocking', 'Cannot use block or shield mechanics', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // No Parry/Counter
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Parry/Counter', 'Parry and counter attack mechanics are disabled', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Parry/Counter', 'Parry and counter attack mechanics are disabled', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // Light Attacks Only
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'Light Attacks Only', 'Cannot use heavy, charged, or power attacks', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'Light Attacks Only', 'Cannot use heavy, charged, or power attacks', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // No Guard Break
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Guard Break', 'Cannot break enemy defense or shields', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Guard Break', 'Cannot break enemy defense or shields', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // No Grabs/Throws
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Grabs/Throws', 'Grappling and throwing mechanics are disabled', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Grabs/Throws', 'Grappling and throwing mechanics are disabled', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // ==================== ABILITY & SKILL RULES ====================

        // Only Last Ability
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'Only Last Ability', 'Can only use the last ability slot, all other abilities disabled', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'Only Last Ability', 'Can only use the last ability slot, all other abilities disabled', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // No Ultimate
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Ultimate', 'Ultimate abilities and super moves are disabled', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Ultimate', 'Ultimate abilities and super moves are disabled', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // ==================== VISION & UI RULES ====================

        // No Crosshair
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Crosshair', 'Aiming reticle is hidden, must aim without assistance', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Crosshair', 'Aiming reticle is hidden, must aim without assistance', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // No HUD
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No HUD', 'All user interface elements are hidden', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No HUD', 'All user interface elements are hidden', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // No Enemy Markers
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Enemy Markers', 'Enemy indicators, tags, and health bars are hidden', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Enemy Markers', 'Enemy indicators, tags, and health bars are hidden', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // ==================== GAME SYSTEMS RULES ====================

        // No Repair
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Repair', 'Cannot repair weapons, equipment, or vehicles - damage is permanent', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Repair', 'Cannot repair weapons, equipment, or vehicles - damage is permanent', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // No Dialog Skip
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Dialog Skip', 'Must listen to all dialogue and cutscenes, cannot skip', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Dialog Skip', 'Must listen to all dialogue and cutscenes, cannot skip', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
    }

    public function down(Schema $schema): void
    {
        // Delete all rules and their difficulty levels (cascade will handle difficulty levels)
        $this->addSql('DELETE FROM rules WHERE id BETWEEN 87 AND 134');
    }
}
