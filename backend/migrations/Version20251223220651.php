<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add RPG-specific challenge rules (Secret of Mana, Undertale, Final Fantasy style)
 */
final class Version20251223220651 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add RPG-specific challenge rules (character, combat, equipment, and playstyle restrictions)';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');
        
        // Define all new rules with their descriptions
        $rules = [
            // Basic Rules (9 levels: 5m, 10m, 15m, 20m, 25m, 30m, 35m, 40m, 45m)
            ['Solo Character Only', 'No party members allowed. Single character run only.', 'basic'],
            ['Starting Character Only', 'Cannot switch to other party members. Use the starting character throughout.', 'basic'],
            ['No Character Switching', 'Stick with one character. No swapping during combat or exploration.', 'basic'],
            ['No Magic', 'Physical attacks only. No spells or magic allowed.', 'basic'],
            ['Magic Only', 'No physical attacks. Spells and magic only for combat.', 'basic'],
            ['No Special Abilities', 'Basic attacks only. No skills, techs, or limit breaks.', 'basic'],
            ['Normal Attacks Only', 'Most basic attack only. No combos or charged attacks.', 'basic'],
            ['Starting Equipment Only', 'Never upgrade weapons or armor. Keep what you start with.', 'basic'],
            ['No Equipment', 'Naked run. No weapons or armor equipped.', 'basic'],
            ['No Armor', 'Weapons allowed, but no defensive gear.', 'basic'],
            ['No Accessories', 'No rings, relics, or charms. Pure stats only.', 'basic'],
            ['No Running', 'Fight every random encounter. Cannot flee from battles.', 'basic'],
            ['No Save Points', 'Limited saves or one life only. Hardcore mode.', 'basic'],
            
            // Court Rules (4 levels: 15m, 30m, 45m, 1h) - Same rules, different difficulty
            ['Solo Character Only', 'No party members allowed. Single character run only.', 'court'],
            ['Starting Character Only', 'Cannot switch to other party members. Use the starting character throughout.', 'court'],
            ['No Character Switching', 'Stick with one character. No swapping during combat or exploration.', 'court'],
            ['No Magic', 'Physical attacks only. No spells or magic allowed.', 'court'],
            ['Magic Only', 'No physical attacks. Spells and magic only for combat.', 'court'],
            ['No Special Abilities', 'Basic attacks only. No skills, techs, or limit breaks.', 'court'],
            ['Normal Attacks Only', 'Most basic attack only. No combos or charged attacks.', 'court'],
            ['Starting Equipment Only', 'Never upgrade weapons or armor. Keep what you start with.', 'court'],
            ['No Equipment', 'Naked run. No weapons or armor equipped.', 'court'],
            ['No Armor', 'Weapons allowed, but no defensive gear.', 'court'],
            ['No Accessories', 'No rings, relics, or charms. Pure stats only.', 'court'],
            ['No Running', 'Fight every random encounter. Cannot flee from battles.', 'court'],
            ['No Save Points', 'Limited saves or one life only. Hardcore mode.', 'court'],
        ];
        
        $startId = 51; // Continue from existing rules (1-50)
        
        foreach ($rules as $index => [$name, $description, $ruleType]) {
            $ruleId = $startId + $index;
            
            // Insert rule
            $this->addSql(
                "INSERT INTO rules (id, name, description, rule_type, created_at) VALUES (?, ?, ?, ?, ?)",
                [$ruleId, $name, $description, $ruleType, $now]
            );
            
            // Insert difficulty levels based on type
            if ($ruleType === 'basic') {
                // Basic: 9 levels (5m, 10m, 15m, 20m, 25m, 30m, 35m, 40m, 45m)
                $durations = [300, 600, 900, 1200, 1500, 1800, 2100, 2400, 2700];
                for ($level = 1; $level <= 9; $level++) {
                    $this->addSql(
                        "INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES (?, ?, ?, ?)",
                        [$ruleId, $level, $durations[$level - 1], $now]
                    );
                }
            } else if ($ruleType === 'court') {
                // Court: 4 levels (15m, 30m, 45m, 1h)
                $durations = [900, 1800, 2700, 3600];
                for ($level = 1; $level <= 4; $level++) {
                    $this->addSql(
                        "INSERT INTO rule_difficulty_levels (rule_id, difficulty_level, duration_minutes, created_at) VALUES (?, ?, ?, ?)",
                        [$ruleId, $level, $durations[$level - 1], $now]
                    );
                }
            }
        }
    }

    public function down(Schema $schema): void
    {
        // Remove all RPG-specific rules and their difficulty levels
        $this->addSql('DELETE FROM rule_difficulty_levels WHERE rule_id BETWEEN 51 AND 76');
        $this->addSql('DELETE FROM rules WHERE id BETWEEN 51 AND 76');
    }
}
