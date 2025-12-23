<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add Call of Duty specific challenge rules (weapon restrictions and playstyle restrictions)
 */
final class Version20251223214209 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add CoD-specific challenge rules (Shotgun, LMG, No Sprinting, No Jumping, Crouch Only, No ADS, No Reloading)';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');
        
        // Define all new rules with their descriptions
        $rules = [
            // Basic Rules (9 levels: 5m, 10m, 15m, 20m, 25m, 30m, 35m, 40m, 45m)
            ['Shotgun Only', 'Only shotguns allowed. Dominate at close range!', 'basic'],
            ['LMG Only', 'Light Machine Guns only. Heavy firepower with slow mobility.', 'basic'],
            ['No Sprinting', 'Sprint is disabled. Move at walking or tactical pace only.', 'basic'],
            ['No Jumping', 'Keep your feet on the ground. No jumping allowed.', 'basic'],
            ['Crouch Only', 'You must stay crouched at all times. Tactical stealth movement.', 'basic'],
            ['No ADS', 'Cannot aim down sights. Hip-fire only for maximum difficulty.', 'basic'],
            ['No Reloading', 'Cannot reload weapons. Switch to another weapon when empty.', 'basic'],
            
            // Court Rules (4 levels: 15m, 30m, 45m, 1h) - Same rules, different difficulty
            ['Shotgun Only', 'Only shotguns allowed. Dominate at close range!', 'court'],
            ['LMG Only', 'Light Machine Guns only. Heavy firepower with slow mobility.', 'court'],
            ['No Sprinting', 'Sprint is disabled. Move at walking or tactical pace only.', 'court'],
            ['No Jumping', 'Keep your feet on the ground. No jumping allowed.', 'court'],
            ['Crouch Only', 'You must stay crouched at all times. Tactical stealth movement.', 'court'],
            ['No ADS', 'Cannot aim down sights. Hip-fire only for maximum difficulty.', 'court'],
            ['No Reloading', 'Cannot reload weapons. Switch to another weapon when empty.', 'court'],
        ];
        
        $startId = 23; // Continue from existing rules (1-22)
        
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
        // Remove all CoD-specific rules and their difficulty levels
        $this->addSql('DELETE FROM rule_difficulty_levels WHERE rule_id BETWEEN 23 AND 36');
        $this->addSql('DELETE FROM rules WHERE id BETWEEN 23 AND 36');
    }
}
