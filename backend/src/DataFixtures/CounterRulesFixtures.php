<?php

namespace App\DataFixtures;

use App\Entity\Rule;
use App\Entity\RuleDifficultyLevel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CounterRulesFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // ===== LEGENDARY COUNTER RULES (Single Count) =====
        $legendaryCounterRules = [
            [
                'name' => 'Kill Yourself',
                'description' => 'You must kill yourself (in-game) once. Intentional death required. Dark challenge for dark times.',
                'amount' => 1,
            ],
            [
                'name' => 'Restart the Game',
                'description' => 'You must restart the entire game once. Close and relaunch. Test your patience and commitment.',
                'amount' => 1,
            ],
            [
                'name' => 'Reload a Save',
                'description' => 'You must reload a save file once. Undo your progress, question your decisions. Time is a circle.',
                'amount' => 1,
            ],
        ];

        foreach ($legendaryCounterRules as $ruleData) {
            $rule = new Rule();
            $rule->setName($ruleData['name']);
            $rule->setDescription($ruleData['description']);
            $rule->setRuleType('legendary');

            $manager->persist($rule);

            // Legendary counter rules: 1 difficulty level, amount only (no duration)
            $difficultyLevel = new RuleDifficultyLevel();
            $difficultyLevel->setRule($rule);
            $difficultyLevel->setDifficultyLevel(1);
            $difficultyLevel->setDurationSeconds(null); // No time limit
            $difficultyLevel->setAmount($ruleData['amount']); // Counter-based

            $manager->persist($difficultyLevel);
        }

        // ===== BASIC COUNTER RULES (1-9 Counts) =====
        $basicCounterRules = [
            // Combat/Damage
            [
                'name' => 'Get Hit by Enemy',
                'description' => 'You must get hit by an enemy X times. Take damage intentionally. Sometimes pain is the path.',
            ],
            [
                'name' => 'Take Fall Damage',
                'description' => 'You must take fall damage X times. Jump off things, embrace gravity. What goes up must come down.',
            ],
            [
                'name' => 'Die to Enemy',
                'description' => 'You must die to an enemy X times. Let them win, temporarily. Death is a teacher.',
            ],

            // Weapon Usage
            [
                'name' => 'Fire Sniper Rifle',
                'description' => 'You must fire a sniper rifle X times. Precision shots required. One shot, one count.',
            ],
            [
                'name' => 'Fire Machine Gun',
                'description' => 'You must fire a machine gun X times (bursts count as one). Spray and pray, count your sins.',
            ],
            [
                'name' => 'Fire Pistol',
                'description' => 'You must fire a pistol X times. Basic weapon, basic count. Every trigger pull matters.',
            ],
            [
                'name' => 'Fire Shotgun',
                'description' => 'You must fire a shotgun X times. Close range devastation. Boom, count, repeat.',
            ],
            [
                'name' => 'Throw Grenade',
                'description' => 'You must throw a grenade X times. Explosions required. Pull pin, throw, count.',
            ],
            [
                'name' => 'Melee Attack',
                'description' => 'You must perform melee attacks X times. Get up close, make it personal. Fists, knives, count them all.',
            ],

            // Resource Usage
            [
                'name' => 'Use Healing Item',
                'description' => 'You must use a healing item X times. Waste your meds, heal when full if needed. Every bandage counts.',
            ],
            [
                'name' => 'Pick Up Item',
                'description' => 'You must pick up items X times. Collect things, hoard stuff. Every pickup is progress.',
            ],
            [
                'name' => 'Drop Item',
                'description' => 'You must drop items X times. Let go, embrace minimalism. Inventory management by force.',
            ],
            [
                'name' => 'Open Container',
                'description' => 'You must open containers/chests X times. Loot boxes, treasure chests, anything that opens. Curiosity counts.',
            ],

            // Movement/Navigation
            [
                'name' => 'Jump',
                'description' => 'You must jump X times. Hop around, bounce for no reason. Every leap is logged.',
            ],
            [
                'name' => 'Sprint',
                'description' => 'You must sprint X times. Run in bursts, waste stamina. Speed demons only.',
            ],
            [
                'name' => 'Crouch',
                'description' => 'You must crouch X times. Duck, hide, look silly. Every squat is sacred.',
            ],
            [
                'name' => 'Enter Safe Room',
                'description' => 'You must enter safe rooms X times. Seek sanctuary repeatedly. Safety is a habit.',
            ],

            // Interaction
            [
                'name' => 'Open Door',
                'description' => 'You must open doors X times. Every entrance is an event. Doorways to destiny.',
            ],
            [
                'name' => 'Use Save Point',
                'description' => 'You must use save points X times. Save scum required. Commit your progress to history.',
            ],
            [
                'name' => 'Talk to NPC',
                'description' => 'You must talk to NPCs X times. Social butterfly challenge. Every conversation counts.',
            ],

            // Special Actions
            [
                'name' => 'Use Special Ability',
                'description' => 'You must use your special ability X times. Magic, skills, ultimates. Show off your power.',
            ],
            [
                'name' => 'Reload Weapon',
                'description' => 'You must reload your weapon X times. Empty those mags, hear that click. Reload enthusiasts only.',
            ],
        ];

        foreach ($basicCounterRules as $ruleData) {
            $rule = new Rule();
            $rule->setName($ruleData['name']);
            $rule->setDescription($ruleData['description']);
            $rule->setRuleType('basic');

            $manager->persist($rule);

            // Basic counter rules: 9 difficulty levels (1-9 counts)
            for ($i = 1; $i <= 9; ++$i) {
                $difficultyLevel = new RuleDifficultyLevel();
                $difficultyLevel->setRule($rule);
                $difficultyLevel->setDifficultyLevel($i);
                $difficultyLevel->setDurationSeconds(null); // No time limit
                $difficultyLevel->setAmount($i); // Counter: 1, 2, 3...9

                $manager->persist($difficultyLevel);
            }
        }

        // ===== COURT COUNTER RULES (1-4 Counts) =====
        $courtCounterRules = [
            [
                'name' => 'Boss Encounter',
                'description' => 'You must encounter bosses X times. Face the big threats. Every boss meeting matters.',
            ],
            [
                'name' => 'Complete Objective',
                'description' => 'You must complete objectives X times. Mission progress required. Check off those tasks.',
            ],
            [
                'name' => 'Find Secret',
                'description' => 'You must find secret areas X times. Exploration rewarded. Hidden paths revealed.',
            ],
            [
                'name' => 'Purchase Item',
                'description' => 'You must purchase items from merchants X times. Shop till you drop. Every transaction tracked.',
            ],
        ];

        foreach ($courtCounterRules as $ruleData) {
            $rule = new Rule();
            $rule->setName($ruleData['name']);
            $rule->setDescription($ruleData['description']);
            $rule->setRuleType('court');

            $manager->persist($rule);

            // Court counter rules: 4 difficulty levels (1-4 counts)
            for ($i = 1; $i <= 4; ++$i) {
                $difficultyLevel = new RuleDifficultyLevel();
                $difficultyLevel->setRule($rule);
                $difficultyLevel->setDifficultyLevel($i);
                $difficultyLevel->setDurationSeconds(null); // No time limit
                $difficultyLevel->setAmount($i); // Counter: 1, 2, 3, 4

                $manager->persist($difficultyLevel);
            }
        }

        $manager->flush();
    }
}
