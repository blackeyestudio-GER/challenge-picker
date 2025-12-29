<?php

namespace App\DataFixtures;

use App\Entity\Rule;
use App\Entity\RuleCategory;
use App\Entity\RuleDifficultyLevel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RuleFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $rulesData = $this->getRulesData();

        foreach ($rulesData as $data) {
            $rule = new Rule();
            $rule->setName($data['name']);
            $rule->setDescription($data['description'] ?? null);
            $rule->setRuleType($data['rule_type']);

            $manager->persist($rule);

            // Add difficulty levels
            foreach ($data['difficulty_levels'] as $levelData) {
                $difficultyLevel = new RuleDifficultyLevel();
                $difficultyLevel->setRule($rule);
                $difficultyLevel->setDifficultyLevel($levelData['level']);
                $difficultyLevel->setDurationSeconds($levelData['duration_seconds']);
                $difficultyLevel->setDescription(null);

                $manager->persist($difficultyLevel);
            }

            // Add category associations
            foreach ($data['category_refs'] as $categoryRef) {
                /** @var \App\Entity\Category $category */
                $category = $this->getReference($categoryRef, \App\Entity\Category::class);
                $ruleCategory = new RuleCategory();
                $ruleCategory->setRule($rule);
                $ruleCategory->setCategory($category);
                $ruleCategory->setManualRelevanceScore(null);

                $manager->persist($ruleCategory);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }

    private function getRulesData(): array
    {
        $rules = [];

        // Helper function to create both basic and court variants
        $addVariants = function ($name, $desc, $categoryRefs) use (&$rules) {
            // Basic variant (10 levels): 60, 120, 180... 600 seconds (1-10 minutes)
            $rules[] = [
                'name' => $name,
                'description' => $desc,
                'rule_type' => 'basic',
                'category_refs' => $categoryRefs,
                'difficulty_levels' => array_map(fn ($i) => [
                    'level' => $i,
                    'duration_seconds' => $i * 60, // 60, 120, 180... 600 seconds
                ], range(1, 10)),
            ];

            // Court variant (4 levels): 600, 900, 1200, 1500 seconds (10, 15, 20, 25 minutes)
            $rules[] = [
                'name' => $name . ' (Court)',
                'description' => $desc . ' - Court variant',
                'rule_type' => 'court',
                'category_refs' => $categoryRefs,
                'difficulty_levels' => array_map(fn ($i) => [
                    'level' => $i,
                    'duration_seconds' => (10 + (($i - 1) * 5)) * 60, // 600, 900, 1200, 1500 seconds
                ], range(1, 4)),
            ];
        };

        // Weapon Restrictions (Shooter + Horror)
        $addVariants('Pistol Only', 'Use only pistols', [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_HORROR]);
        $addVariants('Sniper Only', 'Use only sniper rifles', [CategoryFixtures::CATEGORY_SHOOTER]);
        $addVariants('Knife Only', 'Melee weapons only', [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_HORROR]);
        $addVariants('Machine Gun Only', 'Automatic weapons only', [CategoryFixtures::CATEGORY_SHOOTER]);

        // Horror/Survival Rules
        $addVariants('No Dodging', 'Cannot dodge or evade attacks', [CategoryFixtures::CATEGORY_HORROR]);
        $addVariants('No Healing', 'Cannot use healing items', [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_RPG]);
        $addVariants('No Item Box', 'Cannot use storage boxes', [CategoryFixtures::CATEGORY_HORROR]);
        $addVariants('No Ammo Pickup', 'Cannot pick up ammunition', [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_SHOOTER]);
        $addVariants('No Shop/Merchant', 'Cannot use shop or merchant', [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_RPG]);
        $addVariants('No Damage Allowed', 'Must reload checkpoint if hit', [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_SHOOTER]);
        $addVariants('Walking Only', 'Cannot sprint or run', [CategoryFixtures::CATEGORY_HORROR]);
        $addVariants('No Flashlight', 'Cannot use flashlight or light sources', [CategoryFixtures::CATEGORY_HORROR]);
        $addVariants('No Map', 'Cannot use map or minimap', [CategoryFixtures::CATEGORY_HORROR]);
        $addVariants('No Hiding', 'Cannot hide from enemies', [CategoryFixtures::CATEGORY_HORROR]);
        $addVariants('No Safe Rooms', 'Cannot use safe rooms or safe zones', [CategoryFixtures::CATEGORY_HORROR]);
        $addVariants('Pacifist Mode', 'Cannot kill any enemies - avoid, evade, or run', [CategoryFixtures::CATEGORY_HORROR, CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_RPG]);

        // Shooter Rules
        $addVariants('No Objectives', 'Cannot complete objectives', [CategoryFixtures::CATEGORY_SHOOTER, CategoryFixtures::CATEGORY_BATTLE_ROYALE]);
        $addVariants('No Killstreak Usage', 'Cannot use killstreak rewards', [CategoryFixtures::CATEGORY_SHOOTER]);

        // Battle Royale Rules
        $addVariants('No Shield Pickup', 'Cannot pick up shield/armor items', [CategoryFixtures::CATEGORY_BATTLE_ROYALE]);
        $addVariants('ADS Only', 'Must aim down sights at all times', [CategoryFixtures::CATEGORY_SHOOTER]);
        $addVariants('No Grenades', 'Cannot use grenades or explosives', [CategoryFixtures::CATEGORY_SHOOTER]);
        $addVariants('No Melee', 'Cannot use melee attacks', [CategoryFixtures::CATEGORY_SHOOTER]);
        $addVariants('No Reloading', 'Cannot reload your weapon - must switch weapons when ammo runs out', [CategoryFixtures::CATEGORY_SHOOTER]);
        $addVariants('Crouch Only', 'Must stay crouched at all times', [CategoryFixtures::CATEGORY_SHOOTER]);

        // RPG Rules
        $addVariants('Starting Equipment Only', 'Can only use starting equipment', [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_HORROR]);
        $addVariants('No Equipment', 'Cannot use any equipment', [CategoryFixtures::CATEGORY_RPG]);
        $addVariants('No Armor', 'Cannot equip armor', [CategoryFixtures::CATEGORY_RPG]);
        $addVariants('No Accessories', 'Cannot equip accessories', [CategoryFixtures::CATEGORY_RPG]);
        $addVariants('No Save', 'Cannot save progress', [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_HORROR]);
        $addVariants('No Running', 'Must walk at all times', [CategoryFixtures::CATEGORY_HORROR]);
        $addVariants('No Magic', 'Cannot use magic or spells - physical combat only', [CategoryFixtures::CATEGORY_RPG]);
        $addVariants('Only Magic', 'Can only use magic and spells - no physical weapons', [CategoryFixtures::CATEGORY_RPG]);
        $addVariants('Ranged Only', 'Can only use ranged weapons (bows, crossbows, guns) - no melee', [CategoryFixtures::CATEGORY_RPG, CategoryFixtures::CATEGORY_SHOOTER]);
        $addVariants('No Ranged', 'Cannot use ranged weapons (bows, crossbows, guns) - melee and magic only', [CategoryFixtures::CATEGORY_RPG]);

        // Roguelike Rules
        $addVariants('No Rerolls', 'Cannot reroll item drops or rewards', [CategoryFixtures::CATEGORY_ROGUELIKE]);
        $addVariants('First Item Only', 'Can only use the first item found', [CategoryFixtures::CATEGORY_ROGUELIKE]);
        $addVariants('Cursed Items Only', 'Can only pick up cursed items', [CategoryFixtures::CATEGORY_ROGUELIKE]);
        $addVariants('No Room Clearing', 'Must rush through rooms', [CategoryFixtures::CATEGORY_ROGUELIKE]);

        // MOBA Rules
        $addVariants('No Warding', 'Cannot use wards or vision items', [CategoryFixtures::CATEGORY_MOBA]);
        $addVariants('No Ganking', 'Cannot gank or help other lanes', [CategoryFixtures::CATEGORY_MOBA]);
        $addVariants('No Recall', 'Cannot recall to base', [CategoryFixtures::CATEGORY_MOBA]);

        // Platform/Movement Rules
        $addVariants('No Double Jump', 'Cannot use double jump', [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_METROIDVANIA]);
        $addVariants('No Dash', 'Cannot use dash ability', [CategoryFixtures::CATEGORY_PLATFORMER, CategoryFixtures::CATEGORY_METROIDVANIA]);
        $addVariants('Ground Only', 'Must stay on ground level', [CategoryFixtures::CATEGORY_PLATFORMER]);

        // Fighting Game Rules
        $addVariants('No Blocking', 'Cannot block attacks', [CategoryFixtures::CATEGORY_FIGHTING]);
        $addVariants('No Special Moves', 'Cannot use special moves', [CategoryFixtures::CATEGORY_FIGHTING]);
        $addVariants('Light Attacks Only', 'Can only use light attacks', [CategoryFixtures::CATEGORY_FIGHTING]);

        // Survival Rules
        $addVariants('No Crafting', 'Cannot craft items', [CategoryFixtures::CATEGORY_SURVIVAL]);
        $addVariants('No Building', 'Cannot build structures', [CategoryFixtures::CATEGORY_SURVIVAL]);
        $addVariants('No Base', 'Cannot create a base', [CategoryFixtures::CATEGORY_SURVIVAL]);

        // Universal Balancing Rule - Free Pass/Joker (takes up slot but has no effect)
        $addVariants('Free Pass', 'A joker card with no effect. Use this to reduce active rule count and make challenges easier.', [
            CategoryFixtures::CATEGORY_HORROR,
            CategoryFixtures::CATEGORY_SHOOTER,
            CategoryFixtures::CATEGORY_RPG,
            CategoryFixtures::CATEGORY_PLATFORMER,
            CategoryFixtures::CATEGORY_ROGUELIKE,
            CategoryFixtures::CATEGORY_SOULSLIKE,
            CategoryFixtures::CATEGORY_STRATEGY,
            CategoryFixtures::CATEGORY_MOBA,
            CategoryFixtures::CATEGORY_FIGHTING,
            CategoryFixtures::CATEGORY_SURVIVAL,
            CategoryFixtures::CATEGORY_BATTLE_ROYALE,
        ]);

        // Helper function for MOBA/Strategy rules (30s increments up to 5min, then 1min increments starting from 5min)
        $addMobaStrategyVariants = function ($name, $desc, $categoryRefs) use (&$rules) {
            // Basic variant: 30, 60, 90, 120, 150, 180, 210, 240, 270, 300 seconds (0.5-5 minutes in 30s steps, 10 levels)
            $rules[] = [
                'name' => $name,
                'description' => $desc,
                'rule_type' => 'basic',
                'category_refs' => $categoryRefs,
                'difficulty_levels' => array_map(fn ($i) => [
                    'level' => $i,
                    'duration_seconds' => $i * 30, // 30, 60, 90... 300 seconds
                ], range(1, 10)),
            ];

            // Court variant: 300, 360, 420, 480 seconds (5, 6, 7, 8 minutes, 4 levels)
            $rules[] = [
                'name' => $name . ' (Court)',
                'description' => $desc . ' (Extended duration)',
                'rule_type' => 'court',
                'category_refs' => $categoryRefs,
                'difficulty_levels' => [
                    ['level' => 1, 'duration_seconds' => 300],
                    ['level' => 2, 'duration_seconds' => 360],
                    ['level' => 3, 'duration_seconds' => 420],
                    ['level' => 4, 'duration_seconds' => 480],
                ],
            ];
        };

        // ===== MOBA RULES =====
        $addMobaStrategyVariants('No Last Hitting', 'Cannot deliver killing blow to minions/creeps for gold', [CategoryFixtures::CATEGORY_MOBA]);
        $addMobaStrategyVariants('No Wards', 'Cannot place vision wards or use vision items', [CategoryFixtures::CATEGORY_MOBA]);
        $addMobaStrategyVariants('No Ultimate Ability', 'Cannot use ultimate/R ability', [CategoryFixtures::CATEGORY_MOBA]);
        $addMobaStrategyVariants('No Jungle Camps', 'Cannot farm neutral jungle monsters', [CategoryFixtures::CATEGORY_MOBA]);
        $addMobaStrategyVariants('Starting Lane Only', 'Must stay in initial lane (no roaming)', [CategoryFixtures::CATEGORY_MOBA]);

        // ===== STRATEGY RULES =====
        $addMobaStrategyVariants('No Building Construction', 'Cannot construct new buildings', [CategoryFixtures::CATEGORY_STRATEGY]);
        $addMobaStrategyVariants('No Unit Production', 'Cannot train or produce new units', [CategoryFixtures::CATEGORY_STRATEGY]);
        $addMobaStrategyVariants('No Defensive Structures', 'Cannot build turrets, walls, or defenses', [CategoryFixtures::CATEGORY_STRATEGY]);
        $addMobaStrategyVariants('No Tech Upgrades', 'Cannot research technology or upgrades', [CategoryFixtures::CATEGORY_STRATEGY]);
        $addMobaStrategyVariants('Starting Units Only', 'Can only use starting units', [CategoryFixtures::CATEGORY_STRATEGY]);

        // ===== SURVIVAL RULES (Normal variants: 1-10 min basic, 10/15/20/25 min court) =====
        $addVariants('No Eating Food', 'Cannot consume food items', [CategoryFixtures::CATEGORY_SURVIVAL]);
        $addVariants('No Crafting Items', 'Cannot craft items or tools', [CategoryFixtures::CATEGORY_SURVIVAL]);
        $addVariants('No Building Shelter', 'Cannot build structures or bases', [CategoryFixtures::CATEGORY_SURVIVAL]);
        $addVariants('No Drinking Water', 'Cannot consume water or drinks', [CategoryFixtures::CATEGORY_SURVIVAL]);
        $addVariants('No Resource Gathering', 'Cannot gather wood, stone, ore, etc.', [CategoryFixtures::CATEGORY_SURVIVAL]);

        return $rules;
    }
}
