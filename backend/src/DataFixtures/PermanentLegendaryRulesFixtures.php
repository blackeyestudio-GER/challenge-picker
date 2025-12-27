<?php

namespace App\DataFixtures;

use App\Entity\Rule;
use App\Entity\RuleDifficultyLevel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PermanentLegendaryRulesFixtures extends Fixture
{
    // Weapon & Combat
    public const RULE_KNIFE_ONLY = 'rule_knife_only';
    public const RULE_MELEE_ONLY = 'rule_melee_only';
    public const RULE_PISTOL_ONLY = 'rule_pistol_only';

    // Movement
    public const RULE_ONLY_WALKING = 'rule_only_walking';
    public const RULE_NO_JUMPING = 'rule_no_jumping';

    // Resources
    public const RULE_NO_HEAL = 'rule_no_heal';
    public const RULE_NO_ITEMBOX = 'rule_no_itembox';
    public const RULE_NO_SHOP = 'rule_no_shop';

    // Save & Checkpoint
    public const RULE_NO_SAVE = 'rule_no_save';

    // Shooter Specific
    public const RULE_NO_ADS = 'rule_no_ads';
    public const RULE_NO_GRENADES = 'rule_no_grenades';

    // MOBA Specific
    public const RULE_NO_LAST_HITTING = 'rule_no_last_hitting';
    public const RULE_NO_WARDS = 'rule_no_wards';
    public const RULE_NO_ULTIMATE = 'rule_no_ultimate';
    public const RULE_NO_JUNGLE_CAMPS = 'rule_no_jungle_camps';
    public const RULE_STARTING_LANE_ONLY = 'rule_starting_lane_only';

    // Strategy Specific
    public const RULE_NO_BUILDING = 'rule_no_building';
    public const RULE_NO_UNIT_PRODUCTION = 'rule_no_unit_production';
    public const RULE_NO_DEFENSIVE_STRUCTURES = 'rule_no_defensive_structures';
    public const RULE_NO_TECH_UPGRADES = 'rule_no_tech_upgrades';
    public const RULE_STARTING_UNITS_ONLY = 'rule_starting_units_only';

    public function load(ObjectManager $manager): void
    {
        $rules = [
            // ===== WEAPON & COMBAT RESTRICTIONS =====
            [
                'reference' => self::RULE_KNIFE_ONLY,
                'name' => 'Knife Only',
                'description' => 'You can only use the knife as a weapon. No guns, no explosives, no other melee weapons - only the knife. A true test of skill and patience.',
            ],
            [
                'reference' => self::RULE_MELEE_ONLY,
                'name' => 'Melee Only',
                'description' => 'You can only use melee weapons. No guns, no explosives, no ranged attacks - only close combat. Get up close and personal.',
            ],
            [
                'reference' => self::RULE_PISTOL_ONLY,
                'name' => 'Pistol Only',
                'description' => 'You can only use pistols. No shotguns, no rifles, no heavy weapons. Precision and headshots are your only path to survival.',
            ],
            [
                'reference' => 'rule_no_weapons',
                'name' => 'Pacifist Run',
                'description' => 'You cannot use any weapons to kill enemies. Stealth, running, or environmental kills only. Can you survive without violence?',
            ],

            // ===== MOVEMENT RESTRICTIONS =====
            [
                'reference' => self::RULE_ONLY_WALKING,
                'name' => 'Only Walking',
                'description' => 'You cannot run or sprint. Walking speed only throughout the entire game. Take your time and savor the journey through every danger.',
            ],
            [
                'reference' => 'rule_no_dodging',
                'name' => 'No Dodging',
                'description' => 'You cannot dodge, roll, or evade attacks. Face every threat head-on with no escape option. Perfect timing is no longer an option.',
            ],
            [
                'reference' => self::RULE_NO_JUMPING,
                'name' => 'No Jumping',
                'description' => 'You cannot jump. Find alternative routes and stay grounded. Sometimes the hardest path is the only path.',
            ],
            [
                'reference' => 'rule_pacifist',
                'name' => 'Pacifist',
                'description' => 'You cannot kill any enemies. Avoid, evade, or find non-lethal solutions. Violence is not the answer - prove you can win without taking a single life.',
            ],

            // ===== RESOURCE MANAGEMENT =====
            [
                'reference' => self::RULE_NO_HEAL,
                'name' => 'No Healing',
                'description' => 'You cannot use any healing items. No herbs, no first aid, no medkits - play at the health you have. One hit closer to death with every mistake.',
            ],
            [
                'reference' => 'rule_no_ammo_pickup',
                'name' => 'No Ammo Pickups',
                'description' => 'You cannot pick up ammunition. Start with what you have and make every shot count. When you run out, find another way.',
            ],
            [
                'reference' => self::RULE_NO_ITEMBOX,
                'name' => 'No Item Box',
                'description' => 'You cannot use storage boxes. Everything you carry must fit in your inventory - choose wisely. Every item slot matters.',
            ],
            [
                'reference' => self::RULE_NO_SHOP,
                'name' => 'No Shop',
                'description' => 'You cannot purchase anything from merchants or shops. Scavenge only - no buying your way to victory. Make do with what you find.',
            ],
            [
                'reference' => 'rule_no_item_pickups',
                'name' => 'No Item Pickups',
                'description' => 'You cannot pick up any items except quest items. Start with what you have and finish with what you have. The ultimate minimalist run.',
            ],

            // ===== SAVE & CHECKPOINT RESTRICTIONS =====
            [
                'reference' => self::RULE_NO_SAVE,
                'name' => 'No Saving',
                'description' => 'You cannot save your game. One life, no checkpoints, no safety net - live with every decision you make. Death is permanent.',
            ],
            [
                'reference' => 'rule_permadeath',
                'name' => 'True Permadeath',
                'description' => 'If you die, the playthrough is over. No continues, no retries, no second chances. This is your one and only attempt.',
            ],

            // ===== VISION & AWARENESS RESTRICTIONS =====
            [
                'reference' => 'rule_no_map',
                'name' => 'No Map',
                'description' => 'You cannot use the map or minimap. Navigate by memory and landmarks only. Get lost, find your way, learn the world.',
            ],
            [
                'reference' => 'rule_no_hud',
                'name' => 'No HUD',
                'description' => 'Turn off the entire HUD. No health bars, no ammo count, no objective markers. Pure immersion, pure challenge.',
            ],
            [
                'reference' => 'rule_no_flashlight',
                'name' => 'No Flashlight',
                'description' => 'You cannot use flashlights or any light sources. Navigate in darkness, rely on environmental lighting. Fear the shadows.',
            ],

            // ===== EQUIPMENT & GEAR RESTRICTIONS =====
            [
                'reference' => 'rule_no_armor',
                'name' => 'No Armor',
                'description' => 'You cannot wear or equip any armor. Your flesh is your only protection. Take hits like paper, survive like steel.',
            ],
            [
                'reference' => 'rule_starter_equipment_only',
                'name' => 'Starter Equipment Only',
                'description' => 'You can only use the equipment you start with. No upgrades, no better gear. Beat the game with beginner tools.',
            ],
            [
                'reference' => 'rule_no_consumables',
                'name' => 'No Consumables',
                'description' => 'You cannot use any consumable items. No potions, no buffs, no grenades. Your skills and permanent equipment only.',
            ],

            // ===== PROGRESSION RESTRICTIONS =====
            [
                'reference' => 'rule_no_upgrades',
                'name' => 'No Upgrades',
                'description' => 'You cannot upgrade weapons, skills, or abilities. Stay at base power throughout the entire game. Raw skill over stats.',
            ],
            [
                'reference' => 'rule_no_leveling',
                'name' => 'No Leveling Up',
                'description' => 'You cannot level up or gain experience. Stay at level 1 forever. David vs Goliath, every single fight.',
            ],

            // ===== SOULS-LIKE SPECIFIC =====
            [
                'reference' => 'rule_no_parrying',
                'name' => 'No Parrying',
                'description' => 'You cannot parry attacks. No perfect timing counters, no deflections. Block or dodge only. Master the art of defense without the easy counter.',
            ],
            [
                'reference' => 'rule_no_summons',
                'name' => 'No Summons',
                'description' => 'You cannot summon allies or co-op partners. Face every boss alone, as intended. No help, no backup, just you and the challenge.',
            ],
            [
                'reference' => 'rule_no_shield',
                'name' => 'No Shield',
                'description' => 'You cannot use shields to block. Dodge every attack or take the hit. Aggressive offense is your only defense.',
            ],
            [
                'reference' => 'rule_fat_roll_only',
                'name' => 'Fat Roll Only',
                'description' => 'You must maintain heavy equipment load. Slow, clunky rolls only. Every dodge is a commitment, every movement is calculated.',
            ],

            // ===== SHOOTER SPECIFIC =====
            [
                'reference' => self::RULE_NO_ADS,
                'name' => 'No ADS (Aim Down Sights)',
                'description' => 'You cannot aim down sights. Hipfire only, raw aim skill required. No zoom, no accuracy bonus, just natural instinct.',
            ],
            [
                'reference' => self::RULE_NO_GRENADES,
                'name' => 'No Grenades',
                'description' => 'You cannot use grenades or explosive weapons. Bullets only, precise aim required. No area damage shortcuts.',
            ],
            [
                'reference' => 'rule_primary_weapon_only',
                'name' => 'Primary Weapon Only',
                'description' => 'You cannot use secondary weapons or swap guns. One weapon, entire game. Choose wisely at the start.',
            ],
            [
                'reference' => 'rule_no_killstreaks',
                'name' => 'No Killstreaks',
                'description' => 'You cannot use killstreak rewards or special abilities. Pure gunplay, no power-ups. Skill over streaks.',
            ],

            // ===== RPG SPECIFIC =====
            [
                'reference' => 'rule_no_fast_travel',
                'name' => 'No Fast Travel',
                'description' => 'You cannot fast travel. Walk or ride everywhere, experience the entire journey. Every trip is an adventure, every shortcut is earned.',
            ],
            [
                'reference' => 'rule_no_magic',
                'name' => 'No Magic',
                'description' => 'You cannot use magic or spells. Physical combat only, no arcane shortcuts. Sword and bow, nothing mystical.',
            ],
            [
                'reference' => 'rule_only_magic',
                'name' => 'Only Magic',
                'description' => 'You can only use magic and spells. No physical weapons, no melee, no ranged - pure spellcasting. Embrace the arcane path.',
            ],
            [
                'reference' => 'rule_ranged_only',
                'name' => 'Ranged Only',
                'description' => 'You can only use ranged weapons (bows, crossbows, guns). No melee, no magic - keep your distance. Master of the bow and arrow.',
            ],
            [
                'reference' => 'rule_no_ranged',
                'name' => 'No Ranged',
                'description' => 'You cannot use ranged weapons (bows, crossbows, guns). Melee and magic only - get up close and personal. No coward\'s way out.',
            ],
            [
                'reference' => 'rule_no_crafting',
                'name' => 'No Crafting',
                'description' => 'You cannot craft items. Find or buy only, no creating your own gear. Scavenger mentality required.',
            ],
            [
                'reference' => 'rule_no_quest_markers',
                'name' => 'No Quest Markers',
                'description' => 'You cannot use quest markers or waypoints. Navigate by dialogue and exploration only. Read, listen, discover.',
            ],

            // ===== METROIDVANIA SPECIFIC =====
            [
                'reference' => 'rule_no_backtracking',
                'name' => 'No Backtracking',
                'description' => 'You cannot return to previous areas. Forward progress only, no revisiting. Every choice is permanent, every path is one-way.',
            ],
            [
                'reference' => 'rule_sequence_break_forbidden',
                'name' => 'Sequence Break Forbidden',
                'description' => 'You must follow the intended progression route. No sequence breaks, no skipping. Experience the game as designed.',
            ],

            // ===== ROGUELIKE SPECIFIC =====
            [
                'reference' => 'rule_no_rerolls',
                'name' => 'No Rerolls',
                'description' => 'You cannot reroll items or rewards. Accept what you get, adapt to RNG. First drop is final drop.',
            ],
            [
                'reference' => 'rule_cursed_items_only',
                'name' => 'Cursed Items Only',
                'description' => 'You can only pick up cursed items. Embrace the negative effects, turn weakness into strength. High risk, high reward.',
            ],

            // ===== FIGHTING GAME SPECIFIC =====
            [
                'reference' => 'rule_no_blocking',
                'name' => 'No Blocking',
                'description' => 'You cannot block attacks. Pure offense, dodge or get hit. Aggressive playstyle mandatory.',
            ],
            [
                'reference' => 'rule_light_attacks_only',
                'name' => 'Light Attacks Only',
                'description' => 'You can only use light attacks. No heavy hits, no special moves. Quick jabs and combos only.',
            ],
            [
                'reference' => 'rule_random_character',
                'name' => 'Random Character',
                'description' => 'You must use a randomly selected character. No picking your main, adapt to any fighter. Versatility test.',
            ],

            // ===== MOBA SPECIFIC =====
            [
                'reference' => 'rule_no_warding',
                'name' => 'No Warding',
                'description' => 'You cannot place wards or vision items. Play blind, rely on awareness. Map control through presence, not vision.',
            ],
            [
                'reference' => 'rule_lane_lock',
                'name' => 'Lane Lock',
                'description' => 'You cannot leave your assigned lane. No roaming, no ganking, no helping. Win your lane or lose the game.',
            ],
            [
                'reference' => 'rule_no_recall',
                'name' => 'No Recall',
                'description' => 'You cannot recall to base. Stay in lane until death, manage resources carefully. Base visits are earned through respawns.',
            ],

            // ===== HORROR SPECIFIC (Additional) =====
            [
                'reference' => 'rule_no_safe_rooms',
                'name' => 'No Safe Rooms',
                'description' => 'You cannot enter safe rooms or safe zones. No respite, no sanctuary. Danger is constant, peace is forbidden.',
            ],
            [
                'reference' => 'rule_no_hiding',
                'name' => 'No Hiding',
                'description' => 'You cannot hide from enemies. Lockers, closets, and hiding spots are off-limits. Face your fears, literally.',
            ],

            // ===== SURVIVAL SPECIFIC =====
            [
                'reference' => 'rule_no_base_building',
                'name' => 'No Base Building',
                'description' => 'You cannot build structures or bases. Nomadic survival only, keep moving. Home is where you stand right now.',
            ],
            [
                'reference' => 'rule_no_storage',
                'name' => 'No Storage',
                'description' => 'You cannot use storage containers. Carry everything or drop it forever. Inventory is your only safe place.',
            ],

            // ===== BATTLE ROYALE SPECIFIC =====
            [
                'reference' => 'rule_first_weapon_only',
                'name' => 'First Weapon Only',
                'description' => 'You can only use the first weapon you find. No looting better guns, make it work. First drop determines your fate.',
            ],
            [
                'reference' => 'rule_no_vehicles',
                'name' => 'No Vehicles',
                'description' => 'You cannot use vehicles. Run everywhere, embrace the journey. Your legs are your only transport.',
            ],

            // ===== MOBA SPECIFIC =====
            [
                'reference' => self::RULE_NO_LAST_HITTING,
                'name' => 'No Last Hitting',
                'description' => 'You cannot deliver the killing blow to minions or creeps for gold. Passive gold income only. Deny yourself the farm.',
            ],
            [
                'reference' => self::RULE_NO_WARDS,
                'name' => 'No Wards',
                'description' => 'You cannot place vision wards or use vision items. Play blind, rely on map awareness and intuition. Fog of war is your reality.',
            ],
            [
                'reference' => self::RULE_NO_ULTIMATE,
                'name' => 'No Ultimate Ability',
                'description' => 'You cannot use your ultimate/R ability. Basic abilities and auto-attacks only. Win without your trump card.',
            ],
            [
                'reference' => self::RULE_NO_JUNGLE_CAMPS,
                'name' => 'No Jungle Camps',
                'description' => 'You cannot farm neutral jungle monsters. Lane only for gold and experience. The jungle is forbidden territory.',
            ],
            [
                'reference' => self::RULE_STARTING_LANE_ONLY,
                'name' => 'Starting Lane Only',
                'description' => 'You must stay in your initial lane. No roaming, no lane swapping allowed. Commit to your position.',
            ],

            // ===== STRATEGY SPECIFIC =====
            [
                'reference' => self::RULE_NO_BUILDING,
                'name' => 'No Building Construction',
                'description' => 'You cannot construct new buildings. Use your starting base only. No expansion, pure strategic dominance.',
            ],
            [
                'reference' => self::RULE_NO_UNIT_PRODUCTION,
                'name' => 'No Unit Production',
                'description' => 'You cannot train or produce new units. Starting army only. Every loss is permanent, every unit precious.',
            ],
            [
                'reference' => self::RULE_NO_DEFENSIVE_STRUCTURES,
                'name' => 'No Defensive Structures',
                'description' => 'You cannot build turrets, walls, or defensive structures. Pure offensive or economic play. Defense is cowardice.',
            ],
            [
                'reference' => self::RULE_NO_TECH_UPGRADES,
                'name' => 'No Tech Upgrades',
                'description' => 'You cannot research technology or upgrades. Tier 1 units and tech only. Stone age warfare forever.',
            ],
            [
                'reference' => self::RULE_STARTING_UNITS_ONLY,
                'name' => 'Starting Units Only',
                'description' => 'You can only use the units you start with. No unit production whatsoever. Protect what you have - it\'s all you\'ll ever get.',
            ],
        ];

        foreach ($rules as $ruleData) {
            $rule = new Rule();
            $rule->setName($ruleData['name']);
            $rule->setDescription($ruleData['description']);
            $rule->setRuleType('legendary');

            $manager->persist($rule);

            // Create single difficulty level (legendary rules only have level 1)
            // Permanent rules have NO duration and NO amount - they last the entire playthrough
            $difficultyLevel = new RuleDifficultyLevel();
            $difficultyLevel->setRule($rule);
            $difficultyLevel->setDifficultyLevel(1);
            $difficultyLevel->setDurationSeconds(null); // Permanent - no time limit
            $difficultyLevel->setAmount(null); // Permanent - no counter

            $manager->persist($difficultyLevel);

            $this->addReference($ruleData['reference'], $rule);
        }

        $manager->flush();
    }
}
