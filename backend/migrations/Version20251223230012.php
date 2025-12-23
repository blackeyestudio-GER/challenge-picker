<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Seed Roguelike and MOBA specific challenge rules
 */
final class Version20251223230012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed Roguelike and MOBA specific challenge rules (Basic and Court variants)';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');
        $ruleId = 135;

        // ==================== ROGUELIKE RULES ====================
        
        // No Rerolls
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Rerolls', 'Cannot reroll items, shop offerings, or any random selections', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Rerolls', 'Cannot reroll items, shop offerings, or any random selections', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // First Item Only
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'First Item Only', 'Must take the first item offered in shops or drops, no choosing', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'First Item Only', 'Must take the first item offered in shops or drops, no choosing', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // Cursed Items Only
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'Cursed Items Only', 'Can only pick up cursed, negative, or detrimental items', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'Cursed Items Only', 'Can only pick up cursed, negative, or detrimental items', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // No Room Clearing
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Room Clearing', 'Must rush through rooms without clearing all enemies', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Room Clearing', 'Must rush through rooms without clearing all enemies', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // ==================== MOBA RULES ====================

        // No Warding
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Warding', 'Cannot place wards or vision items on the map', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Warding', 'Cannot place wards or vision items on the map', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // No Ganking
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Ganking', 'Cannot leave your lane to gank other lanes', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Ganking', 'Cannot leave your lane to gank other lanes', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        // No Recall
        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Recall', 'Cannot use recall/teleport to return to base', 'basic', '$now')");
        for ($level = 1; $level <= 9; $level++) {
            $duration = $level * 60;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
        $ruleId++;

        $this->addSql("INSERT INTO rules (id, name, description, rule_type, created_at) VALUES ($ruleId, 'No Recall', 'Cannot use recall/teleport to return to base', 'court', '$now')");
        for ($level = 1; $level <= 4; $level++) {
            $duration = $level * 300;
            $this->addSql("INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES ($ruleId, $level, $duration, '$now')");
        }
    }

    public function down(Schema $schema): void
    {
        // Delete all rules and their difficulty levels (cascade will handle difficulty levels)
        $this->addSql('DELETE FROM rules WHERE id BETWEEN 135 AND 148');
    }
}
