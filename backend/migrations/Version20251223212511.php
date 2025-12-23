<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Seed initial challenge rules for horror/survival games (Resident Evil style)
 */
final class Version20251223212511 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed initial challenge rules with variants (Basic and Court types)';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');
        
        // Define all rules with their descriptions
        $rules = [
            // Basic Rules (9 levels: 5m, 10m, 15m, 20m, 25m, 30m, 35m, 40m, 45m)
            ['Pistol Only', 'You can only use pistols throughout the game. No other weapons allowed.', 'basic'],
            ['Sniper Only', 'You can only use sniper rifles. Test your precision and patience.', 'basic'],
            ['Knife Only', 'Melee weapons only. No guns allowed. Up close and personal!', 'basic'],
            ['Machine Gun Only', 'Only machine guns and submachine guns allowed. Spray and pray!', 'basic'],
            ['No Dodging', 'Dodge/evade mechanics are forbidden. Face enemies head-on.', 'basic'],
            ['No Healing', 'Cannot use healing items. Your health is all you get!', 'basic'],
            ['No Item Box', 'Storage boxes are off-limits. Manage your inventory carefully!', 'basic'],
            ['No Ammo Pickup', 'Cannot pick up ammo from the environment. Start with what you have!', 'basic'],
            ['No Shop/Merchant', 'Cannot buy or sell items at shops. Survive with what you find.', 'basic'],
            ['No Damage Allowed', 'If you take any damage, reload from last checkpoint. Perfect play required!', 'basic'],
            ['Take Damage From Any Enemy', 'You must take damage from every enemy you encounter. Strategic health management!', 'basic'],
            
            // Court Rules (4 levels: 15m, 30m, 45m, 1h) - Same rules, different difficulty
            ['Pistol Only', 'You can only use pistols throughout the game. No other weapons allowed.', 'court'],
            ['Sniper Only', 'You can only use sniper rifles. Test your precision and patience.', 'court'],
            ['Knife Only', 'Melee weapons only. No guns allowed. Up close and personal!', 'court'],
            ['Machine Gun Only', 'Only machine guns and submachine guns allowed. Spray and pray!', 'court'],
            ['No Dodging', 'Dodge/evade mechanics are forbidden. Face enemies head-on.', 'court'],
            ['No Healing', 'Cannot use healing items. Your health is all you get!', 'court'],
            ['No Item Box', 'Storage boxes are off-limits. Manage your inventory carefully!', 'court'],
            ['No Ammo Pickup', 'Cannot pick up ammo from the environment. Start with what you have!', 'court'],
            ['No Shop/Merchant', 'Cannot buy or sell items at shops. Survive with what you find.', 'court'],
            ['No Damage Allowed', 'If you take any damage, reload from last checkpoint. Perfect play required!', 'court'],
            ['Take Damage From Any Enemy', 'You must take damage from every enemy you encounter. Strategic health management!', 'court'],
        ];
        
        foreach ($rules as $index => [$name, $description, $ruleType]) {
            $ruleId = $index + 1;
            
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
        // Remove all seeded rules and their difficulty levels
        $this->addSql('DELETE FROM rule_difficulty_levels WHERE rule_id BETWEEN 1 AND 22');
        $this->addSql('DELETE FROM rules WHERE id BETWEEN 1 AND 22');
    }
}
