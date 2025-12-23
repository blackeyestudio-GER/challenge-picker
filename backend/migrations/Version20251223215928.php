<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add more CoD-specific challenge rules (objectives, killstreaks, ADS, equipment, melee restrictions)
 */
final class Version20251223215928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add CoD-specific challenge rules (No Objectives, No Killstreaks, ADS Only, Iron Sights Only, No Side Weapons, No Grenades, No Melee)';
    }

    public function up(Schema $schema): void
    {
        $now = date('Y-m-d H:i:s');
        
        // Define all new rules with their descriptions
        $rules = [
            // Basic Rules (9 levels: 5m, 10m, 15m, 20m, 25m, 30m, 35m, 40m, 45m)
            ['No Objectives', 'Ignore the objective. Focus on kills only!', 'basic'],
            ['No Killstreaks', 'Cannot use earned killstreaks. Earn them but never call them in.', 'basic'],
            ['ADS Only', 'Must always aim down sights. No hip-fire allowed.', 'basic'],
            ['Iron Sights Only', 'No optics or scopes. Iron sights only for aiming.', 'basic'],
            ['No Side Weapons', 'Cannot use lethal or tactical equipment. Primary/secondary weapons only.', 'basic'],
            ['No Grenades', 'No explosive grenades allowed. Find other ways to deal damage.', 'basic'],
            ['No Melee', 'Melee attacks are forbidden. Keep your distance!', 'basic'],
            
            // Court Rules (4 levels: 15m, 30m, 45m, 1h) - Same rules, different difficulty
            ['No Objectives', 'Ignore the objective. Focus on kills only!', 'court'],
            ['No Killstreaks', 'Cannot use earned killstreaks. Earn them but never call them in.', 'court'],
            ['ADS Only', 'Must always aim down sights. No hip-fire allowed.', 'court'],
            ['Iron Sights Only', 'No optics or scopes. Iron sights only for aiming.', 'court'],
            ['No Side Weapons', 'Cannot use lethal or tactical equipment. Primary/secondary weapons only.', 'court'],
            ['No Grenades', 'No explosive grenades allowed. Find other ways to deal damage.', 'court'],
            ['No Melee', 'Melee attacks are forbidden. Keep your distance!', 'court'],
        ];
        
        $startId = 37; // Continue from existing rules (1-36)
        
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
        // Remove all additional CoD-specific rules and their difficulty levels
        $this->addSql('DELETE FROM rule_difficulty_levels WHERE rule_id BETWEEN 37 AND 50');
        $this->addSql('DELETE FROM rules WHERE id BETWEEN 37 AND 50');
    }
}
