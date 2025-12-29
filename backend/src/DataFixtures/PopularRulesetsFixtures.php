<?php

namespace App\DataFixtures;

use App\Entity\Game;
use App\Entity\Rule;
use App\Entity\Ruleset;
use App\Entity\RulesetRuleCard;
use App\Repository\GameRepository;
use App\Repository\RuleRepository;
use App\Repository\TarotCardRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PopularRulesetsFixtures extends Fixture implements DependentFixtureInterface
{
    private GameRepository $gameRepository;
    private RuleRepository $ruleRepository;
    private TarotCardRepository $tarotCardRepository;

    public function __construct(
        GameRepository $gameRepository,
        RuleRepository $ruleRepository,
        TarotCardRepository $tarotCardRepository
    ) {
        $this->gameRepository = $gameRepository;
        $this->ruleRepository = $ruleRepository;
        $this->tarotCardRepository = $tarotCardRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $rulesets = $this->getRulesetsData();

        // Get all tarot cards for assigning
        $tarotCards = $this->tarotCardRepository->findAll();
        if (empty($tarotCards)) {
            throw new \RuntimeException('No tarot cards found. Make sure TarotCardFixtures are loaded first.');
        }

        foreach ($rulesets as $rulesetData) {
            $ruleset = new Ruleset();
            $ruleset->setName($rulesetData['name']);
            $ruleset->setDescription($rulesetData['description']);

            // Add games to ruleset by name
            foreach ($rulesetData['games'] as $gameName) {
                $game = $this->gameRepository->findOneBy(['name' => $gameName]);
                if ($game) {
                    $ruleset->addGame($game);
                }
            }

            $manager->persist($ruleset);

            // Add rules to ruleset
            $position = 0;
            foreach ($rulesetData['rules'] as $ruleData) {
                $rule = null;

                // Try to get rule by reference first (for permanent legendary rules)
                if (isset($ruleData['reference'])) {
                    try {
                        $rule = $this->getReference($ruleData['reference'], Rule::class);
                    } catch (\OutOfBoundsException $e) {
                        // Reference doesn't exist, try by name
                    }
                }

                // If no rule found yet, query by name
                if (!$rule && isset($ruleData['name'])) {
                    $rule = $this->ruleRepository->findOneBy(['name' => $ruleData['name']]);
                }

                if ($rule) {
                    $rulesetRuleCard = new RulesetRuleCard();
                    $rulesetRuleCard->setRuleset($ruleset);
                    $rulesetRuleCard->setRule($rule);
                    $rulesetRuleCard->setPosition($position++);
                    $rulesetRuleCard->setIsDefault($ruleData['is_default']);

                    // Assign a random tarot card (for now - will be improved later)
                    $randomCard = $tarotCards[array_rand($tarotCards)];
                    $rulesetRuleCard->setTarotCard($randomCard);

                    $manager->persist($rulesetRuleCard);
                }
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameFixtures::class,
            RuleFixtures::class,
            CounterRulesFixtures::class,
            PermanentLegendaryRulesFixtures::class,
            TarotCardFixtures::class,
        ];
    }

    /**
     * @return array<int, array{name: string, description: string, games: array<int, string>, rules: array<int, array{reference?: string, name?: string, is_default: bool}>}>
     */
    private function getRulesetsData(): array
    {
        return [
            // ========================================
            // RESIDENT EVIL - KNIFE ONLY CHALLENGE
            // ========================================
            [
                'name' => 'Resident Evil: Knife Only Hardcore',
                'description' => 'The ultimate survival horror challenge. Complete the game using only your knife, with no item box access and limited healing. Every encounter is life or death.',
                'games' => ['Horror'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'name' => 'Knife Only', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ITEMBOX, 'name' => 'No Item Box', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'name' => 'No Saving', 'is_default' => true],
                    ['name' => 'Use Heal Item', 'is_default' => false], // Counter: managed in-game
                ],
            ],

            // ========================================
            // DARK SOULS - SL1 NO ROLLING
            // ========================================
            [
                'name' => 'Dark Souls: Soul Level 1 Challenge',
                'description' => 'Beat the game at Soul Level 1 with no rolling. Pure skill, perfect positioning, and unwavering determination required.',
                'games' => ['Dark Souls (2011)', 'Dark Souls II (2014)', 'Dark Souls III (2016)', 'Elden Ring (2022)', 'Bloodborne (2015)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'name' => 'No Healing', 'is_default' => true],
                    ['name' => 'Die to Enemy', 'is_default' => false], // Counter
                ],
            ],

            // ========================================
            // ZELDA - THREE HEART CHALLENGE
            // ========================================
            [
                'name' => 'Zelda: Three Heart Hero',
                'description' => 'Complete your adventure with only 3 hearts. No heart containers, no healing, just pure skill and careful gameplay.',
                'games' => ['The Legend of Zelda: Ocarina of Time (1998)', 'The Legend of Zelda: Majora\'s Mask (2000)', 'The Legend of Zelda: Twilight Princess (2006)', 'The Legend of Zelda: Breath of the Wild (2017)', 'The Legend of Zelda: Tears of the Kingdom (2023)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'name' => 'No Healing', 'is_default' => true],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false], // Counter
                ],
            ],

            // ========================================
            // UNDERTALE - TRUE PACIFIST
            // ========================================
            [
                'name' => 'Undertale: True Pacifist Run',
                'description' => 'Show mercy to every enemy. Complete the game without killing a single monster. Friendship and determination are your only weapons.',
                'games' => ['Undertale (2015)'],
                'rules' => [
                    ['reference' => 'rule_pacifist', 'name' => 'Pacifist', 'is_default' => true],
                    ['name' => 'Talk to NPC', 'is_default' => false], // Counter
                ],
            ],

            // ========================================
            // XCOM - IRONMAN MODE
            // ========================================
            [
                'name' => 'XCOM: Ironman Impossible',
                'description' => 'True permadeath. No save scumming, no retries. Every decision is final. Lose a soldier, they\'re gone forever.',
                'games' => ['XCOM 2 (2016)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'name' => 'No Saving', 'is_default' => true],
                ],
            ],

            // ========================================
            // HALO - LASO (Legendary All Skulls On)
            // ========================================
            [
                'name' => 'Halo: LASO Challenge',
                'description' => 'Legendary difficulty with maximum restrictions. No HUD, limited ammo, aggressive enemies. The ultimate Spartan test.',
                'games' => ['Halo: Combat Evolved (2001)', 'Halo 2 (2004)', 'Halo 3 (2007)', 'Halo Infinite (2021)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'name' => 'No Healing', 'is_default' => true],
                    ['name' => 'Die to Enemy', 'is_default' => false], // Counter
                ],
            ],

            // ========================================
            // ROGUELIKE - NO REROLLS MASTERY
            // ========================================
            [
                'name' => 'Roguelike: Pure RNG Challenge',
                'description' => 'Accept what the gods give you. No rerolls, no skips. Adapt or die - that\'s the roguelike way.',
                'games' => ['Hades (2020)', 'Dead Cells (2018)', 'The Binding of Isaac: Rebirth (2014)', 'Enter the Gungeon (2016)', 'Nuclear Throne (2015)'],
                'rules' => [
                    // Rules TBD - need roguelike-specific permanent rules
                ],
            ],

            // ========================================
            // SURVIVAL - HARDCORE MODE
            // ========================================
            [
                'name' => 'Survival: Hardcore Realism',
                'description' => 'The ultimate survival test. No saves, no shops. Scavenge and survive with what you find.',
                'games' => ['The Forest (2018)', 'Subnautica (2018)', 'Valheim (2021)', 'Don\'t Starve Together (2016)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'name' => 'No Saving', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SHOP, 'name' => 'No Shop', 'is_default' => true],
                    ['name' => 'Use Heal Item', 'is_default' => false], // Counter
                ],
            ],

            // ========================================
            // RPG - MINIMALIST RUN
            // ========================================
            [
                'name' => 'RPG: Minimalist Hero',
                'description' => 'Beat the epic adventure with minimal resources. No shops, solo journey.',
                'games' => ['RPG'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SHOP, 'name' => 'No Shop', 'is_default' => true],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                ],
            ],

            // ========================================
            // HORROR - TOTAL DARKNESS
            // ========================================
            [
                'name' => 'Horror: Total Darkness',
                'description' => 'Face your fears in complete darkness. No flashlight, no light sources. Let the shadows consume you.',
                'games' => ['Horror'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'name' => 'No Saving', 'is_default' => true],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Healing', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['name' => 'Reload a Save', 'is_default' => false],
                ],
            ],

            // ========================================
            // SPEEDRUN - ANY% CHALLENGE
            // ========================================
            [
                'name' => 'Speedrun: Any% World Record Attempt',
                'description' => 'Beat the game as fast as possible. No restrictions, just pure speed and optimization.',
                'games' => ['Horror'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'Complete Objective', 'is_default' => false], // Counter
                ],
            ],

            // ========================================
            // NUZLOCKE - POKEMON CHALLENGE
            // ========================================
            [
                'name' => 'Pokemon: Classic Nuzlocke',
                'description' => 'The legendary Pokemon challenge. Permadeath for fainted Pokemon, no healing items in battle.',
                'games' => ['Pokemon Red (1996)', 'Pokemon Gold (1999)', 'Pokemon Ruby (2002)', 'Pokemon Platinum (2008)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'name' => 'No Healing', 'is_default' => true],
                ],
            ],

            // ========================================
            // METROIDVANIA - NO BACKTRACKING
            // ========================================
            [
                'name' => 'Metroidvania: Linear Speedrun',
                'description' => 'No backtracking allowed. Once you leave an area, you can never return. Choose your path wisely.',
                'games' => ['Platformer'], // Category-universal ruleset
                'rules' => [
                    // Rules TBD - need metroidvania-specific permanent rules
                ],
            ],

            // ========================================
            // RETRO - NO DAMAGE PERFECT RUN
            // ========================================
            [
                'name' => 'Retro: Hitless Legend',
                'description' => 'Complete classic games without taking a single hit. Pixel-perfect execution required.',
                'games' => ['Mega Man 2 (1988)', 'Castlevania (1986)', 'Contra (1987)', 'Ninja Gaiden (1988)'],
                'rules' => [
                    ['name' => 'Get Hit by Enemy', 'is_default' => false], // Counter: 0 hits allowed
                ],
            ],

            // ========================================
            // RESIDENT EVIL - KNIFE ONLY CHALLENGES
            // ========================================
            [
                'name' => 'RE4 Remake: Minimalist Achievement',
                'description' => 'The official challenge. Complete RE4 Remake using only the Combat Knife. Viewers vote for challenges: No Healing (1-10 min), Pacifist mode (1-10 min), Get hit X times, Reload save penalty, or Instant death.',
                'games' => ['Horror'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'name' => 'Knife Only', 'is_default' => true],
                    // Optional viewer-voted rules:
                    ['name' => 'No Healing', 'is_default' => false], // Timer: 1-10 min
                    ['name' => 'Pacifist Mode', 'is_default' => false], // Timer: 1-10 min
                    ['name' => 'Get Hit by Enemy', 'is_default' => false], // Counter: 1-9 hits
                    ['name' => 'Reload a Save', 'is_default' => false], // Legendary: Reload (1 use)
                    ['name' => 'Kill Yourself', 'is_default' => false], // Legendary: Death
                ],
            ],

            [
                'name' => 'RE2 Remake: Knife Only Challenge',
                'description' => 'Leon and Claire vs Raccoon City with only a knife. Viewers vote: No Healing (1-10 min), Pacifist (1-10 min), Get hit X times, Reload save, or Death.',
                'games' => ['Horror'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'name' => 'Knife Only', 'is_default' => true],
                    ['name' => 'No Healing', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Reload a Save', 'is_default' => false],
                    ['name' => 'Kill Yourself', 'is_default' => false],
                ],
            ],

            [
                'name' => 'RE3 Remake: Knife vs Nemesis',
                'description' => 'Face Nemesis with only a knife. Viewers control the chaos: No Healing (1-10 min), Pacifist (1-10 min), Get hit X times, Reload save, or Death.',
                'games' => ['Horror'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'name' => 'Knife Only', 'is_default' => true],
                    ['name' => 'No Healing', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Reload a Save', 'is_default' => false],
                    ['name' => 'Kill Yourself', 'is_default' => false],
                ],
            ],

            [
                'name' => 'RE7: Survival Knife Challenge',
                'description' => 'First-person horror with only a knife. Viewers decide: No Healing (1-10 min), Pacifist (1-10 min), Get hit X times, Reload save, or Death.',
                'games' => ['Horror'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'name' => 'Knife Only', 'is_default' => true],
                    ['name' => 'No Healing', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Reload a Save', 'is_default' => false],
                    ['name' => 'Kill Yourself', 'is_default' => false],
                ],
            ],

            [
                'name' => 'RE Village: Karambit Only',
                'description' => 'Lycans and bosses with only the Karambit. Viewers add: No Healing (1-10 min), Pacifist (1-10 min), Get hit X times, Reload save, or Death.',
                'games' => ['Horror'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'name' => 'Knife Only', 'is_default' => true],
                    ['name' => 'No Healing', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Reload a Save', 'is_default' => false],
                    ['name' => 'Kill Yourself', 'is_default' => false],
                ],
            ],

            // ========================================
            // RESIDENT EVIL CLASSIC - TANK CONTROL ERA
            // ========================================
            [
                'name' => 'Classic RE: S+ Rank Survival',
                'description' => 'No saves, no item box. Pure S+ rank speedrun attempt with tank controls. Every second counts, every mistake is permanent.',
                'games' => ['Horror'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'name' => 'No Save', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ITEMBOX, 'name' => 'No Item Box', 'is_default' => true],
                    ['name' => 'No Healing', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['name' => 'Knife Only', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Kill Yourself', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Classic RE: Knife vs Tank Controls',
                'description' => 'Combat knife only through fixed camera angles. Master hitboxes and dodge patterns with the most iconic melee weapon.',
                'games' => ['Horror'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'name' => 'Knife Only', 'is_default' => true],
                    ['name' => 'No Healing', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ITEMBOX, 'name' => 'No Item Box', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'name' => 'No Save', 'is_default' => false],
                    ['name' => 'Reload a Save', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Classic RE: Minimalist Horror',
                'description' => 'No item box, carry everything in 8 slots. Every pickup is a strategic decision. Leave nothing behind or lose it forever.',
                'games' => ['Horror'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ITEMBOX, 'name' => 'No Item Box', 'is_default' => true],
                    ['name' => 'Knife Only', 'is_default' => false],
                    ['name' => 'No Healing', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'name' => 'No Save', 'is_default' => false],
                    ['name' => 'Reload a Save', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Classic RE: Permadeath Challenge',
                'description' => 'No saves allowed. One death equals game over. Full arsenal available, but mistakes are permanent. High stakes, high reward.',
                'games' => ['Horror'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'name' => 'No Save', 'is_default' => true],
                    ['name' => 'Knife Only', 'is_default' => false],
                    ['name' => 'No Healing', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ITEMBOX, 'name' => 'No Item Box', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Kill Yourself', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Classic RE: Tank Control Nightmare',
                'description' => 'Walking only, no healing. Slow methodical horror where every encounter is life or death. Abuse save rooms to survive.',
                'games' => ['Horror'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_ONLY_WALKING, 'name' => 'Only Walking', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'name' => 'No Healing', 'is_default' => true],
                    ['name' => 'Knife Only', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ITEMBOX, 'name' => 'No Item Box', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'name' => 'No Save', 'is_default' => false],
                    ['name' => 'Reload a Save', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Classic RE: The Pure Chaos Run',
                'description' => 'No default rules. Viewers build the nightmare from scratch. Will it be easy or impossible? Let chat decide.',
                'games' => ['Horror'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'Knife Only', 'is_default' => false],
                    ['name' => 'No Healing', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ITEMBOX, 'name' => 'No Item Box', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'name' => 'No Save', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Reload a Save', 'is_default' => false],
                ],
            ],

            // ========================================
            // CALL OF DUTY - CHAOS RULESETS
            // ========================================
            [
                'name' => 'CoD: Pistol Pete\'s Revenge',
                'description' => 'Secondary weapon only. Prove pistols can dominate. Viewers add restrictions: hip fire, crouch, walk, melee, no jump, no grenades, ADS only.',
                'games' => [
                    'Call of Duty (2003)', 'Call of Duty 2 (2005)', 'Call of Duty 4: Modern Warfare (2007)',
                    'Call of Duty: Black Ops (2010)', 'Call of Duty: Black Ops 2 (2012)', 'Call of Duty: Black Ops 3 (2015)',
                    'Call of Duty: Black Ops 4 (2018)', 'Call of Duty: Black Ops Cold War (2020)', 'Call of Duty: Modern Warfare (2019)',
                    'Call of Duty: Modern Warfare 2 (2009)', 'Call of Duty: Modern Warfare 3 (2011)', 'Call of Duty: Modern Warfare II (2022)',
                    'Call of Duty: Modern Warfare III (2023)', 'Call of Duty: Vanguard (2021)', 'Call of Duty: Warzone',
                ],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_PISTOL_ONLY, 'name' => 'Pistol Only', 'is_default' => true],
                    ['name' => 'No ADS (Aim Down Sights)', 'is_default' => false],
                    ['name' => 'Crouch', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Melee Only', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'No Grenades', 'is_default' => false],
                    ['name' => 'ADS Only', 'is_default' => false],
                ],
            ],

            [
                'name' => 'CoD: Hip Fire Hero',
                'description' => 'No ADS allowed. Hip fire or go home. Spray and pray! Viewers stack: walk, pistol, crouch, melee, no jump, sniper, no grenades.',
                'games' => [
                    'Call of Duty (2003)', 'Call of Duty 2 (2005)', 'Call of Duty 4: Modern Warfare (2007)',
                    'Call of Duty: Black Ops (2010)', 'Call of Duty: Black Ops 2 (2012)', 'Call of Duty: Black Ops 3 (2015)',
                    'Call of Duty: Black Ops 4 (2018)', 'Call of Duty: Black Ops Cold War (2020)', 'Call of Duty: Modern Warfare (2019)',
                    'Call of Duty: Modern Warfare 2 (2009)', 'Call of Duty: Modern Warfare 3 (2011)', 'Call of Duty: Modern Warfare II (2022)',
                    'Call of Duty: Modern Warfare III (2023)', 'Call of Duty: Vanguard (2021)', 'Call of Duty: Warzone',
                ],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ADS, 'name' => 'No ADS (Aim Down Sights)', 'is_default' => true],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Pistol Only', 'is_default' => false],
                    ['name' => 'Crouch', 'is_default' => false],
                    ['name' => 'Melee Only', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'Sniper Only', 'is_default' => false],
                    ['name' => 'No Grenades', 'is_default' => false],
                ],
            ],

            [
                'name' => 'CoD: The Scope Addict',
                'description' => 'Must ALWAYS aim down sights. Even when running. Even when dying. Viewers add: walk, sniper, crouch, no jump, pistol, melee, no grenades.',
                'games' => [
                    'Call of Duty (2003)', 'Call of Duty 2 (2005)', 'Call of Duty 4: Modern Warfare (2007)',
                    'Call of Duty: Black Ops (2010)', 'Call of Duty: Black Ops 2 (2012)', 'Call of Duty: Black Ops 3 (2015)',
                    'Call of Duty: Black Ops 4 (2018)', 'Call of Duty: Black Ops Cold War (2020)', 'Call of Duty: Modern Warfare (2019)',
                    'Call of Duty: Modern Warfare 2 (2009)', 'Call of Duty: Modern Warfare 3 (2011)', 'Call of Duty: Modern Warfare II (2022)',
                    'Call of Duty: Modern Warfare III (2023)', 'Call of Duty: Vanguard (2021)', 'Call of Duty: Warzone',
                ],
                'rules' => [
                    ['name' => 'ADS Only', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Sniper Only', 'is_default' => false],
                    ['name' => 'Crouch', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'Pistol Only', 'is_default' => false],
                    ['name' => 'Melee Only', 'is_default' => false],
                    ['name' => 'No Grenades', 'is_default' => false],
                ],
            ],

            [
                'name' => 'CoD: Sniper Nightmare',
                'description' => 'Sniper rifle only. Hardscope or die. Viewers control: ADS only, walk, crouch, no jump, no ADS, melee, no grenades.',
                'games' => [
                    'Call of Duty (2003)', 'Call of Duty 2 (2005)', 'Call of Duty 4: Modern Warfare (2007)',
                    'Call of Duty: Black Ops (2010)', 'Call of Duty: Black Ops 2 (2012)', 'Call of Duty: Black Ops 3 (2015)',
                    'Call of Duty: Black Ops 4 (2018)', 'Call of Duty: Black Ops Cold War (2020)', 'Call of Duty: Modern Warfare (2019)',
                    'Call of Duty: Modern Warfare 2 (2009)', 'Call of Duty: Modern Warfare 3 (2011)', 'Call of Duty: Modern Warfare II (2022)',
                    'Call of Duty: Modern Warfare III (2023)', 'Call of Duty: Vanguard (2021)', 'Call of Duty: Warzone',
                ],
                'rules' => [
                    ['name' => 'Sniper Only', 'is_default' => false],
                    ['name' => 'ADS Only', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Crouch', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'No ADS (Aim Down Sights)', 'is_default' => false],
                    ['name' => 'Melee Only', 'is_default' => false],
                    ['name' => 'No Grenades', 'is_default' => false],
                ],
            ],

            [
                'name' => 'CoD: Walking Simulator',
                'description' => 'No sprint, no tactical sprint. Just walk. Slowly. Viewers add: crouch, pistol, no ADS, melee, no jump, sniper, ADS only, no grenades.',
                'games' => [
                    'Call of Duty (2003)', 'Call of Duty 2 (2005)', 'Call of Duty 4: Modern Warfare (2007)',
                    'Call of Duty: Black Ops (2010)', 'Call of Duty: Black Ops 2 (2012)', 'Call of Duty: Black Ops 3 (2015)',
                    'Call of Duty: Black Ops 4 (2018)', 'Call of Duty: Black Ops Cold War (2020)', 'Call of Duty: Modern Warfare (2019)',
                    'Call of Duty: Modern Warfare 2 (2009)', 'Call of Duty: Modern Warfare 3 (2011)', 'Call of Duty: Modern Warfare II (2022)',
                    'Call of Duty: Modern Warfare III (2023)', 'Call of Duty: Vanguard (2021)', 'Call of Duty: Warzone',
                ],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_ONLY_WALKING, 'name' => 'Only Walking', 'is_default' => true],
                    ['name' => 'Crouch', 'is_default' => false],
                    ['name' => 'Pistol Only', 'is_default' => false],
                    ['name' => 'No ADS (Aim Down Sights)', 'is_default' => false],
                    ['name' => 'Melee Only', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'Sniper Only', 'is_default' => false],
                    ['name' => 'ADS Only', 'is_default' => false],
                ],
            ],

            [
                'name' => 'CoD: The Crouch Warrior',
                'description' => 'Entire match crouching. Your knees will never forgive you. Viewers stack: pistol, no ADS, melee, walk, no jump, sniper, ADS only, no grenades.',
                'games' => [
                    'Call of Duty (2003)', 'Call of Duty 2 (2005)', 'Call of Duty 4: Modern Warfare (2007)',
                    'Call of Duty: Black Ops (2010)', 'Call of Duty: Black Ops 2 (2012)', 'Call of Duty: Black Ops 3 (2015)',
                    'Call of Duty: Black Ops 4 (2018)', 'Call of Duty: Black Ops Cold War (2020)', 'Call of Duty: Modern Warfare (2019)',
                    'Call of Duty: Modern Warfare 2 (2009)', 'Call of Duty: Modern Warfare 3 (2011)', 'Call of Duty: Modern Warfare II (2022)',
                    'Call of Duty: Modern Warfare III (2023)', 'Call of Duty: Vanguard (2021)', 'Call of Duty: Warzone',
                ],
                'rules' => [
                    ['name' => 'Crouch', 'is_default' => false],
                    ['name' => 'Pistol Only', 'is_default' => false],
                    ['name' => 'No ADS (Aim Down Sights)', 'is_default' => false],
                    ['name' => 'Melee Only', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'Sniper Only', 'is_default' => false],
                    ['name' => 'ADS Only', 'is_default' => false],
                ],
            ],

            [
                'name' => 'CoD: Knife Party',
                'description' => 'Melee only. Become the ultimate Chad. Rush everything. Viewers add: walk, crouch, no jump, ADS only, no grenades, no ADS, sniper.',
                'games' => [
                    'Call of Duty (2003)', 'Call of Duty 2 (2005)', 'Call of Duty 4: Modern Warfare (2007)',
                    'Call of Duty: Black Ops (2010)', 'Call of Duty: Black Ops 2 (2012)', 'Call of Duty: Black Ops 3 (2015)',
                    'Call of Duty: Black Ops 4 (2018)', 'Call of Duty: Black Ops Cold War (2020)', 'Call of Duty: Modern Warfare (2019)',
                    'Call of Duty: Modern Warfare 2 (2009)', 'Call of Duty: Modern Warfare 3 (2011)', 'Call of Duty: Modern Warfare II (2022)',
                    'Call of Duty: Modern Warfare III (2023)', 'Call of Duty: Vanguard (2021)', 'Call of Duty: Warzone',
                ],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_MELEE_ONLY, 'name' => 'Melee Only', 'is_default' => true],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Crouch', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'ADS Only', 'is_default' => false],
                    ['name' => 'No Grenades', 'is_default' => false],
                    ['name' => 'No ADS (Aim Down Sights)', 'is_default' => false],
                    ['name' => 'Sniper Only', 'is_default' => false],
                ],
            ],

            [
                'name' => 'CoD: Tactical Turtle',
                'description' => 'Crouch walk everywhere. Slowest game of CoD ever. Viewers add: pistol, no ADS, melee, no jump, sniper, ADS only, no grenades.',
                'games' => [
                    'Call of Duty (2003)', 'Call of Duty 2 (2005)', 'Call of Duty 4: Modern Warfare (2007)',
                    'Call of Duty: Black Ops (2010)', 'Call of Duty: Black Ops 2 (2012)', 'Call of Duty: Black Ops 3 (2015)',
                    'Call of Duty: Black Ops 4 (2018)', 'Call of Duty: Black Ops Cold War (2020)', 'Call of Duty: Modern Warfare (2019)',
                    'Call of Duty: Modern Warfare 2 (2009)', 'Call of Duty: Modern Warfare 3 (2011)', 'Call of Duty: Modern Warfare II (2022)',
                    'Call of Duty: Modern Warfare III (2023)', 'Call of Duty: Vanguard (2021)', 'Call of Duty: Warzone',
                ],
                'rules' => [
                    ['name' => 'Crouch', 'is_default' => false],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_ONLY_WALKING, 'name' => 'Only Walking', 'is_default' => true],
                    ['name' => 'Pistol Only', 'is_default' => false],
                    ['name' => 'No ADS (Aim Down Sights)', 'is_default' => false],
                    ['name' => 'Melee Only', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'Sniper Only', 'is_default' => false],
                    ['name' => 'ADS Only', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            [
                'name' => 'CoD: Jump Ban Challenge',
                'description' => 'No jumping. Bunny hoppers not allowed. Stay grounded. Viewers add: walk, crouch, pistol, no ADS, melee, sniper, ADS only, no grenades.',
                'games' => [
                    'Call of Duty (2003)', 'Call of Duty 2 (2005)', 'Call of Duty 4: Modern Warfare (2007)',
                    'Call of Duty: Black Ops (2010)', 'Call of Duty: Black Ops 2 (2012)', 'Call of Duty: Black Ops 3 (2015)',
                    'Call of Duty: Black Ops 4 (2018)', 'Call of Duty: Black Ops Cold War (2020)', 'Call of Duty: Modern Warfare (2019)',
                    'Call of Duty: Modern Warfare 2 (2009)', 'Call of Duty: Modern Warfare 3 (2011)', 'Call of Duty: Modern Warfare II (2022)',
                    'Call of Duty: Modern Warfare III (2023)', 'Call of Duty: Vanguard (2021)', 'Call of Duty: Warzone',
                ],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_JUMPING, 'name' => 'No Jumping', 'is_default' => true],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Crouch', 'is_default' => false],
                    ['name' => 'Pistol Only', 'is_default' => false],
                    ['name' => 'No ADS (Aim Down Sights)', 'is_default' => false],
                    ['name' => 'Melee Only', 'is_default' => false],
                    ['name' => 'Sniper Only', 'is_default' => false],
                    ['name' => 'ADS Only', 'is_default' => false],
                ],
            ],

            [
                'name' => 'CoD: The Chaos Cocktail',
                'description' => 'No permanent rules. Viewers stack ALL restrictions. Pure chaos. Choose from: pistol, no ADS, crouch, walk, no jump, melee, sniper, ADS only.',
                'games' => [
                    'Call of Duty (2003)', 'Call of Duty 2 (2005)', 'Call of Duty 4: Modern Warfare (2007)',
                    'Call of Duty: Black Ops (2010)', 'Call of Duty: Black Ops 2 (2012)', 'Call of Duty: Black Ops 3 (2015)',
                    'Call of Duty: Black Ops 4 (2018)', 'Call of Duty: Black Ops Cold War (2020)', 'Call of Duty: Modern Warfare (2019)',
                    'Call of Duty: Modern Warfare 2 (2009)', 'Call of Duty: Modern Warfare 3 (2011)', 'Call of Duty: Modern Warfare II (2022)',
                    'Call of Duty: Modern Warfare III (2023)', 'Call of Duty: Vanguard (2021)', 'Call of Duty: Warzone',
                ],
                'rules' => [
                    ['name' => 'Pistol Only', 'is_default' => false],
                    ['name' => 'No ADS (Aim Down Sights)', 'is_default' => false],
                    ['name' => 'Crouch', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'Melee Only', 'is_default' => false],
                    ['name' => 'Sniper Only', 'is_default' => false],
                    ['name' => 'ADS Only', 'is_default' => false],
                ],
            ],

            // ========================================
            // RETRO SHOOTER CATEGORY - UNIVERSAL RULESETS
            // ========================================
            [
                'name' => 'Retro: Pistol Start Challenge',
                'description' => 'Starting pistol only. No weapon pickups. Classic challenge. Viewers add: limited heals, limited hits, limited shots, no ammo, save reload.',
                'games' => ['Retro Shooter'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_PISTOL_ONLY, 'name' => 'Pistol Only', 'is_default' => true],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Fire Pistol', 'is_default' => false],
                    ['name' => 'No Ammo Pickups', 'is_default' => false],
                    ['name' => 'Reload a Save', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Retro: First Weapon Challenge',
                'description' => 'First weapon pickup only. No switching. Stick with your initial find. Viewers add: limited heals, limited hits, no ammo, instant death, free pass.',
                'games' => ['Retro Shooter'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'First Weapon Only', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'No Ammo Pickups', 'is_default' => false],
                    ['name' => 'Kill Yourself', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Retro: No Powerups Hardcore',
                'description' => 'No armor, limited healing. Pure skill, no buffs. Viewers add: pistol only, very limited hits/heals, no ammo, save reload, free pass.',
                'games' => ['Retro Shooter'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Armor', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Pistol Only', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'No Ammo Pickups', 'is_default' => false],
                    ['name' => 'Reload a Save', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Retro: Limited Resources',
                'description' => 'Strategic resource management. Limited ammo and healing. Viewers add: limited heals, pistol only, limited hits, no upgrades, save reload, free pass.',
                'games' => ['Retro Shooter'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'No Ammo Pickups', 'is_default' => false],
                    ['name' => 'Pistol Only', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'No Upgrades', 'is_default' => false],
                    ['name' => 'Reload a Save', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Retro: Melee Brawler',
                'description' => 'Melee only. Fists, chainsaw, or knife. Get up close and personal. Viewers add: limited hits, no armor, instant death, free pass.',
                'games' => ['Retro Shooter'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_MELEE_ONLY, 'name' => 'Melee Only', 'is_default' => true],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'No Armor', 'is_default' => false],
                    ['name' => 'No Ammo Pickups', 'is_default' => false],
                    ['name' => 'Kill Yourself', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Retro: One Life Nightmare',
                'description' => 'No saves, no armor. One run, no mistakes. Maximum tension. Viewers add: pistol only, very limited hits, instant death, free pass.',
                'games' => ['Retro Shooter'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'name' => 'No Save', 'is_default' => true],
                    ['name' => 'No Armor', 'is_default' => false],
                    ['name' => 'Pistol Only', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Kill Yourself', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Retro: Pure Chaos Run',
                'description' => 'No defaults. Viewers build the challenge from scratch. Pure community control. Anything goes.',
                'games' => ['Retro Shooter'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'Pistol Only', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'No Ammo Pickups', 'is_default' => false],
                    ['name' => 'Melee Only', 'is_default' => false],
                    ['name' => 'No Armor', 'is_default' => false],
                    ['name' => 'Shotgun Only', 'is_default' => false],
                ],
            ],

            // ========================================
            // PLATFORMER CATEGORY - UNIVERSAL RULESETS
            // ========================================
            [
                'name' => 'Platform: No Abilities Run',
                'description' => 'No double jump, no dash. Base movement only. Pure platforming skill. Viewers add: no dash, walking, limited deaths, no powerups, limited pickups.',
                'games' => ['Platformer'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Double Jump', 'is_default' => false],
                    ['name' => 'No Dash', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'No Upgrades', 'is_default' => false],
                    ['name' => 'Pick Up Item', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Platform: Movement Master',
                'description' => 'No dash ability. Precision movement without speed. Careful platforming. Viewers add: no double jump, walking, limited deaths, no powerups, free pass.',
                'games' => ['Platformer'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Dash', 'is_default' => false],
                    ['name' => 'No Double Jump', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'No Upgrades', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Platform: Hitless Perfectionist',
                'description' => 'No damage allowed. Must reload checkpoint if hit. Perfect execution required. Viewers add: no dash, walking, very limited deaths, instant death, free pass.',
                'games' => ['Platformer'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Damage Allowed', 'is_default' => false],
                    ['name' => 'No Double Jump', 'is_default' => false],
                    ['name' => 'No Dash', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Kill Yourself', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Platform: No Powerups Challenge',
                'description' => 'No upgrades, no collectibles. Base abilities only. Pure skill platforming. Viewers add: no double jump, no dash, walking, limited pickups, free pass.',
                'games' => ['Platformer'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Upgrades', 'is_default' => false],
                    ['name' => 'No Double Jump', 'is_default' => false],
                    ['name' => 'No Dash', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Pick Up Item', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Platform: Minimalist Runner',
                'description' => 'Limited interactions. Minimal collectibles. Pure movement skill. Viewers add: walking, no double jump, no dash, instant death, limited pickups, free pass.',
                'games' => ['Platformer'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Double Jump', 'is_default' => false],
                    ['name' => 'No Dash', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Pick Up Item', 'is_default' => false],
                    ['name' => 'No Upgrades', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Platform: One Death Challenge',
                'description' => 'No damage allowed, one intentional death allowed. High stakes perfect platforming. Viewers add: no double jump, walking, instant death, free pass.',
                'games' => ['Platformer'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Damage Allowed', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'No Double Jump', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Kill Yourself', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Platform: Pure Chaos Jump',
                'description' => 'No defaults. Viewers build the challenge from scratch. Pure community control. Anything goes.',
                'games' => ['Platformer'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Double Jump', 'is_default' => false],
                    ['name' => 'No Dash', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Pick Up Item', 'is_default' => false],
                    ['name' => 'No Upgrades', 'is_default' => false],
                ],
            ],

            // ========================================
            // HORROR CATEGORY - UNIVERSAL RULESETS
            // ========================================
            [
                'name' => 'Horror: No Healing Challenge',
                'description' => 'Survive without any healing items. Pure health management. Viewers control: walk only, limited hits, knife combat, pacifist mode, save reload, or free pass.',
                'games' => ['Horror'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'name' => 'No Healing', 'is_default' => true],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Knife Only', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['name' => 'Reload a Save', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Horror: Stealth Survivor',
                'description' => 'Avoid all combat, pure stealth and evasion. Mandatory boss fights allowed. Viewers add: walk only, limited hits, no healing, no item box, save reload.',
                'games' => ['Horror'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'Pacifist', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'No Healing', 'is_default' => false],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ITEMBOX, 'name' => 'No Item Box', 'is_default' => false],
                    ['name' => 'Reload a Save', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Horror: Resource Scavenger',
                'description' => 'No item box, carry everything. Limited resources, strategic management required. Viewers add: no healing, walk only, knife only, limited hits, pacifist, free pass.',
                'games' => ['Horror'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ITEMBOX, 'name' => 'No Item Box', 'is_default' => true],
                    ['name' => 'No Healing', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Knife Only', 'is_default' => false],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Horror: Close Combat Terror',
                'description' => 'Knife/melee only + no healing. Face your fears up close with no recovery. Viewers add: walk only, pacifist, no item box, save reload, free pass.',
                'games' => ['Horror'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'name' => 'Knife Only', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'name' => 'No Healing', 'is_default' => true],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ITEMBOX, 'name' => 'No Item Box', 'is_default' => false],
                    ['name' => 'Reload a Save', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            [
                'name' => 'Horror: Permadeath Nightmare',
                'description' => 'No saves + walking only. One death = game over. Maximum tension with limited healing. Viewers add: heal counter, knife only, pacifist, instant death, free pass.',
                'games' => ['Horror'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'name' => 'No Save', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_ONLY_WALKING, 'name' => 'Only Walking', 'is_default' => true],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Knife Only', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['name' => 'Kill Yourself', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // ========================================
            // RPG CATEGORY - UNIVERSAL RULESETS
            // ========================================

            // 1. Pacifist Playthrough (Easy)
            [
                'name' => 'RPG: Pacifist Playthrough',
                'description' => 'No shop, starting gear only. Avoid combat when possible. Viewers add: pacifist mode, heal counter, walking only, die to enemy.',
                'games' => ['RPG'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SHOP, 'name' => 'No Shop', 'is_default' => true],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                ],
            ],

            // 2. No Healing Challenge (Medium)
            [
                'name' => 'RPG: No Healing Challenge',
                'description' => 'No healing items allowed. Every hit matters. Viewers add: pacifist mode, walking only, heal counter, die to enemy.',
                'games' => ['RPG'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'name' => 'No Healing', 'is_default' => true],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                ],
            ],

            // 3. Minimalist Run (Hard)
            [
                'name' => 'RPG: Minimalist Run',
                'description' => 'No shop. Scavenge only, limited healing, instant death. Viewers add: walking only, no healing (timed), instant death, free pass.',
                'games' => ['RPG'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SHOP, 'name' => 'No Shop', 'is_default' => true],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'Kill Yourself', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 4. Melee Purist (Medium)
            [
                'name' => 'RPG: Melee Purist',
                'description' => 'Melee weapons only - no ranged heroes or weapons allowed. No healing (timed), walk only, pacifist, die to enemy. Pure close combat.',
                'games' => ['RPG'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_MELEE_ONLY, 'name' => 'Melee Only', 'is_default' => true],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                ],
            ],

            // 5. Ultimate Challenge (Expert)
            [
                'name' => 'RPG: Ultimate Challenge',
                'description' => 'No upgrades allowed. Viewers add: pacifist mode, walking only, instant death, free pass.',
                'games' => ['RPG'], // Category-universal ruleset
                'rules' => [
                    ['reference' => 'rule_no_upgrades', 'name' => 'No Upgrades', 'is_default' => true],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Kill Yourself', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 6. Chaos Mode (Easy)
            [
                'name' => 'RPG: Chaos Mode',
                'description' => 'Pure chaos with no permanent rules. Viewers add: pacifist mode, walking only, no healing (timed), die to enemy.',
                'games' => ['RPG'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                ],
            ],

            // 7. Permadeath Hardcore (Expert)
            [
                'name' => 'RPG: Permadeath Hardcore',
                'description' => 'No saves allowed. One death = game over. Viewers add: walking only (timed), no healing (timed), instant death, free pass.',
                'games' => ['RPG'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'name' => 'No Save', 'is_default' => true],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'Kill Yourself', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 8. Pure Mage Run (Medium)
            [
                'name' => 'RPG: Pure Mage Run',
                'description' => 'Magic and spells only - no physical weapons allowed. Viewers add: no healing (timed), walking only, heal counter, die to enemy, pacifist mode.',
                'games' => ['RPG'], // Category-universal ruleset
                'rules' => [
                    ['reference' => 'rule_only_magic', 'name' => 'Only Magic', 'is_default' => true],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                ],
            ],

            // 9. No Magic Warrior (Medium)
            [
                'name' => 'RPG: No Magic Warrior',
                'description' => 'Physical combat only - no magic or spells allowed. Viewers add: no healing (timed), walking only, heal counter, die to enemy, pacifist mode.',
                'games' => ['RPG'], // Category-universal ruleset
                'rules' => [
                    ['reference' => 'rule_no_magic', 'name' => 'No Magic', 'is_default' => true],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                ],
            ],

            // 10. Spellcaster's Trial (Hard)
            [
                'name' => 'RPG: Spellcaster\'s Trial',
                'description' => 'Only magic + no shop. Pure spellcasting with no upgrades. Viewers add: no healing (timed), walking only, instant death, heal counter, free pass.',
                'games' => [
                    'The Elder Scrolls V: Skyrim (2011)', 'The Witcher 3: Wild Hunt (2015)', 'Dragon Age: Inquisition (2014)',
                    'Divinity: Original Sin 2 (2017)', 'Baldur\'s Gate 3 (2023)', 'Dark Souls (2011)', 'Dark Souls II (2014)',
                    'Dark Souls III (2016)', 'Elden Ring (2022)', 'Demon\'s Souls (2020)', 'Dragon\'s Dogma: Dark Arisen (2013)',
                ],
                'rules' => [
                    ['reference' => 'rule_only_magic', 'name' => 'Only Magic', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SHOP, 'name' => 'No Shop', 'is_default' => true],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Kill Yourself', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 11. Barbarian's Path (Hard)
            [
                'name' => 'RPG: Barbarian\'s Path',
                'description' => 'No magic + melee only. Pure brute force barbarian. Viewers add: no healing (timed), walking only, instant death, heal counter, free pass.',
                'games' => [
                    'The Elder Scrolls V: Skyrim (2011)', 'The Witcher 3: Wild Hunt (2015)', 'Dragon Age: Inquisition (2014)',
                    'Divinity: Original Sin 2 (2017)', 'Baldur\'s Gate 3 (2023)', 'Dark Souls (2011)', 'Dark Souls II (2014)',
                    'Dark Souls III (2016)', 'Elden Ring (2022)', 'Demon\'s Souls (2020)', 'Dragon\'s Dogma: Dark Arisen (2013)',
                    'Kingdom Come: Deliverance (2018)', 'For Honor (2017)',
                ],
                'rules' => [
                    ['reference' => 'rule_no_magic', 'name' => 'No Magic', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_MELEE_ONLY, 'name' => 'Melee Only', 'is_default' => true],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Kill Yourself', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 12. The Confused Magician (Medium)
            [
                'name' => 'RPG: The Confused Magician',
                'description' => 'A spellcaster who forgot how to use magic properly. Only magic allowed, but no healing magic - must use items. Viewers add: no healing (timed), walking only, heal counter, die to enemy, pacifist mode.',
                'games' => ['RPG'], // Category-universal ruleset
                'rules' => [
                    ['reference' => 'rule_only_magic', 'name' => 'Only Magic', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'name' => 'No Healing', 'is_default' => true],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                ],
            ],

            // 13. Everything is Magic (Medium)
            [
                'name' => 'RPG: Everything is Magic',
                'description' => 'Magic only, but without the luxury of shops. Pure spellcasting with scavenged resources. Viewers add: no healing (timed), walking only, heal counter, die to enemy, pacifist mode.',
                'games' => ['RPG'], // Category-universal ruleset
                'rules' => [
                    ['reference' => 'rule_only_magic', 'name' => 'Only Magic', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SHOP, 'name' => 'No Shop', 'is_default' => true],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                ],
            ],

            // 14. Magic is Healing (Medium)
            [
                'name' => 'RPG: Magic is Healing',
                'description' => 'Magic only allowed. No healing items - only healing via magic spells. Strategic spellcasting required. Viewers add: no shop, walking only, heal counter, die to enemy, pacifist mode.',
                'games' => ['RPG'], // Category-universal ruleset
                'rules' => [
                    ['reference' => 'rule_only_magic', 'name' => 'Only Magic', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'name' => 'No Healing Items', 'is_default' => true],
                    ['name' => 'No Shop/Merchant', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                ],
            ],

            // ========================================
            // RPG CATEGORY - RANGED/ARCHERY RULESETS
            // ========================================

            // 15. Archer's Trial (Easy)
            [
                'name' => 'RPG: Archer\'s Trial',
                'description' => 'Ranged weapons only - master the bow and arrow. Viewers add: no healing (timed), walking only, heal counter, die to enemy, pacifist mode.',
                'games' => [
                    'The Elder Scrolls V: Skyrim (2011)', 'The Witcher 3: Wild Hunt (2015)', 'Fallout 3 (2008)', 'Fallout 4 (2015)',
                    'Fallout: New Vegas (2010)', 'Dark Souls (2011)', 'Dark Souls II (2014)', 'Dark Souls III (2016)',
                    'Elden Ring (2022)', 'Demon\'s Souls (2020)', 'Dragon\'s Dogma: Dark Arisen (2013)', 'Horizon Zero Dawn (2017)',
                    'Horizon Forbidden West (2022)', 'Far Cry 3 (2012)', 'Far Cry 4 (2014)', 'Far Cry 5 (2018)',
                    'Tomb Raider (2013)', 'Rise of the Tomb Raider (2015)', 'Shadow of the Tomb Raider (2018)',
                ],
                'rules' => [
                    ['reference' => 'rule_ranged_only', 'name' => 'Ranged Only', 'is_default' => true],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                ],
            ],

            // 16. Sniper Elite (Medium)
            [
                'name' => 'RPG: Sniper Elite',
                'description' => 'Ranged only + no healing items. Every shot counts, no room for error. Viewers add: no shop, walking only, heal counter, die to enemy, pacifist mode.',
                'games' => ['RPG'], // Category-universal ruleset
                'rules' => [
                    ['reference' => 'rule_ranged_only', 'name' => 'Ranged Only', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'name' => 'No Healing', 'is_default' => true],
                    ['name' => 'No Shop/Merchant', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                ],
            ],

            // 17. The Huntsman (Medium)
            [
                'name' => 'RPG: The Huntsman',
                'description' => 'Ranged only + no shops. Hunt your prey with found gear only. Viewers add: no healing (timed), walking only, heal counter, die to enemy, pacifist mode.',
                'games' => ['RPG'], // Category-universal ruleset
                'rules' => [
                    ['reference' => 'rule_ranged_only', 'name' => 'Ranged Only', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SHOP, 'name' => 'No Shop', 'is_default' => true],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                ],
            ],

            // 18. Glass Cannon (Hard)
            [
                'name' => 'RPG: Glass Cannon',
                'description' => 'Ranged only + no armor. High damage, zero defense. Viewers add: no healing (timed), walking only, instant death, heal counter, free pass.',
                'games' => ['RPG'], // Category-universal ruleset
                'rules' => [
                    ['reference' => 'rule_ranged_only', 'name' => 'Ranged Only', 'is_default' => true],
                    ['name' => 'No Armor', 'is_default' => false],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Kill Yourself', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 19. Master Archer (Expert)
            [
                'name' => 'RPG: Master Archer',
                'description' => 'Ranged only + no upgrades + walking only. Peak archery mastery required. Viewers add: no healing (timed), instant death, heal counter, die to enemy, free pass.',
                'games' => ['RPG'], // Category-universal ruleset
                'rules' => [
                    ['reference' => 'rule_ranged_only', 'name' => 'Ranged Only', 'is_default' => true],
                    ['reference' => 'rule_no_upgrades', 'name' => 'No Upgrades', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_ONLY_WALKING, 'name' => 'Walking Only', 'is_default' => true],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'Kill Yourself', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // ========================================
            // RPG CATEGORY - BARBARIAN RULESET
            // ========================================

            // 20. The True Barbarian (Expert)
            [
                'name' => 'RPG: The True Barbarian',
                'description' => 'No ranged + no magic + melee only. Pure brute force warrior. Viewers add: pacifist, no heal, no running, no damage, free pass.',
                'games' => ['RPG'], // Category-universal ruleset
                'rules' => [
                    ['reference' => 'rule_no_ranged', 'name' => 'No Ranged', 'is_default' => true],
                    ['reference' => 'rule_no_magic', 'name' => 'No Magic', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_MELEE_ONLY, 'name' => 'Melee Only', 'is_default' => true],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Damage Allowed', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // ========================================
            // SOULS-LIKE CATEGORY - UNIVERSAL RULESETS (15 TOTAL)
            // ========================================

            // 1. Souls: Shield-less Warrior (Easy)
            [
                'name' => 'Souls: Shield-less Warrior',
                'description' => 'No shield protection. Face enemies with pure skill and timing. Viewers add: no armor, walking only, no healing, die to enemy counter.',
                'games' => ['Souls-like'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Shield Pickup', 'is_default' => false],
                    ['name' => 'No Armor', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Healing', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                ],
            ],

            // 2. Souls: No Healing Run (Medium)
            [
                'name' => 'Souls: No Healing Run',
                'description' => 'Permanent no healing. Every mistake is permanent. Viewers add: no blocking, walking, pacifist mode, heal counter, death counter.',
                'games' => ['Souls-like'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'name' => 'No Healing', 'is_default' => true],
                    ['name' => 'No Blocking', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                ],
            ],

            // 3. Souls: No Rolling Challenge (Medium)
            [
                'name' => 'Souls: No Rolling Challenge',
                'description' => 'No dodge rolling allowed. Master blocking and positioning. Viewers add: no blocking, walking, timed no healing, death counter, free pass.',
                'games' => ['Souls-like'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Dodging', 'is_default' => false],
                    ['name' => 'No Blocking', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'No Healing', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 4. Souls: Melee Purist (Easy)
            [
                'name' => 'Souls: Melee Purist',
                'description' => 'Melee weapons only. No magic, no ranged. Pure close combat mastery. Viewers add: no dodging, no blocking (court), no armor, walking, no healing.',
                'games' => ['Souls-like'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_MELEE_ONLY, 'name' => 'Melee Only', 'is_default' => true],
                    ['name' => 'No Dodging', 'is_default' => false],
                    ['name' => 'No Blocking (Court)', 'is_default' => false],
                    ['name' => 'No Armor', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Healing', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                ],
            ],

            // 5. Souls: No Magic Warrior (Medium)
            [
                'name' => 'Souls: No Magic Warrior',
                'description' => 'Physical combat only - no spells or magic. Viewers add: no armor, no shield, walking, no healing, death counter, free pass.',
                'games' => ['Souls-like'], // Category-universal ruleset
                'rules' => [
                    ['reference' => 'rule_no_magic', 'name' => 'No Magic', 'is_default' => true],
                    ['name' => 'No Armor', 'is_default' => false],
                    ['name' => 'No Shield Pickup', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Healing', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 6. Souls: Naked & Afraid (Hard)
            [
                'name' => 'Souls: Naked & Afraid',
                'description' => 'No armor, no shield protection. Pure vulnerability. Viewers add: timed no healing, walking, basic no healing, instant death, free pass.',
                'games' => ['Souls-like'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Armor', 'is_default' => false],
                    ['name' => 'No Shield Pickup', 'is_default' => false],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'No Healing', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 7. Souls: True Mastery (Expert)
            [
                'name' => 'Souls: True Mastery',
                'description' => 'The ultimate challenge: no dodging, no blocking, no healing. Perfect positioning required. Viewers add: walking, instant death, heal counter, pacifist, free pass.',
                'games' => ['Souls-like'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Dodging', 'is_default' => false],
                    ['name' => 'No Blocking', 'is_default' => false],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'name' => 'No Healing', 'is_default' => true],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Use Heal Item', 'is_default' => false],
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 8. Souls: Glass Cannon (Hard)
            [
                'name' => 'Souls: Glass Cannon',
                'description' => 'No armor + melee only. High risk, high reward combat. Viewers add: walking, timed no healing, no dodging, no blocking, instant death, free pass.',
                'games' => ['Souls-like'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Armor', 'is_default' => false],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_MELEE_ONLY, 'name' => 'Melee Only', 'is_default' => true],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'No Dodging', 'is_default' => false],
                    ['name' => 'No Blocking', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 9. Souls: Pacifist Speedrun (Medium)
            [
                'name' => 'Souls: Pacifist Speedrun',
                'description' => 'Avoid all optional combat. Speedrun mentality with minimal fighting. Viewers add: pacifist mode, timed no healing, walking, no armor (court), no jumping, no dodging (court).',
                'games' => ['Souls-like'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'Pacifist Mode', 'is_default' => false],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Armor (Court)', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'No Dodging (Court)', 'is_default' => false],
                ],
            ],

            // 10. Souls: Hitless Legend (Expert)
            [
                'name' => 'Souls: Hitless Legend',
                'description' => 'No upgrades, no armor. Base stats only. Viewers add: timed no healing, no dodging (court), no blocking, instant death, free pass.',
                'games' => ['Souls-like'], // Category-universal ruleset
                'rules' => [
                    ['reference' => 'rule_no_upgrades', 'name' => 'No Upgrades', 'is_default' => true],
                    ['name' => 'No Armor', 'is_default' => false],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'No Dodging (Court)', 'is_default' => false],
                    ['name' => 'No Blocking', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 11. Souls: Scavenger's Path (Medium) - No Shop + No Upgrades Court
            [
                'name' => 'Souls: Scavenger\'s Path',
                'description' => 'No merchants, no upgrades (timed). Pure scavenging and skill. Viewers add: timed no upgrades, no healing, walking, no armor, death counter, free pass.',
                'games' => ['Souls-like'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SHOP, 'name' => 'No Shop', 'is_default' => true],
                    ['name' => 'No Upgrades (Court)', 'is_default' => false],
                    ['name' => 'No Healing', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Armor', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 12. Souls: Pure Skill Run (Hard) - No Shop + No Upgrades Court
            [
                'name' => 'Souls: Pure Skill Run',
                'description' => 'No shop, no armor, timed no upgrades. Raw skill vs brutal difficulty. Viewers add: timed no upgrades/healing, no dodging, instant death, free pass.',
                'games' => ['Souls-like'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SHOP, 'name' => 'No Shop', 'is_default' => true],
                    ['name' => 'No Armor', 'is_default' => false],
                    ['name' => 'No Upgrades (Court)', 'is_default' => false],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'No Dodging', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 13. Souls: Minimalist Hollow (Medium)
            [
                'name' => 'Souls: Minimalist Hollow',
                'description' => 'No merchants allowed. Physical combat only, no magic. Viewers add: timed no healing, no magic, walking, no shield, death counter, free pass.',
                'games' => ['Souls-like'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SHOP, 'name' => 'No Shop', 'is_default' => true],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'No Magic', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Shield Pickup', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 14. Souls: Merchant's Curse (Hard)
            [
                'name' => 'Souls: Merchant\'s Curse',
                'description' => 'Merchants have abandoned you. No shop access. Viewers add: timed no armor/healing/dodging, walking, instant death, free pass.',
                'games' => ['Souls-like'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SHOP, 'name' => 'No Shop', 'is_default' => true],
                    ['name' => 'No Armor (Court)', 'is_default' => false],
                    ['name' => 'No Healing (Court)', 'is_default' => false],
                    ['name' => 'No Dodging (Court)', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 15. Souls: Starting Gear Only (Easy)
            [
                'name' => 'Souls: Starting Gear Only',
                'description' => 'No merchants, no upgrades. Use only what you start with. Viewers add: no healing, no armor, walking, no shield, death counter.',
                'games' => ['Souls-like'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SHOP, 'name' => 'No Shop', 'is_default' => true],
                    ['name' => 'No Healing', 'is_default' => false],
                    ['name' => 'No Armor', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Shield Pickup', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                ],
            ],

            // ========================================
            // MOBA CATEGORY - UNIVERSAL RULESETS (10 TOTAL)
            // ========================================

            // 1. MOBA: Support Struggle (Easy)
            [
                'name' => 'MOBA: Support Struggle',
                'description' => 'Support life is hard. No vision control, no jungle access, stay in lane. Viewers add: no wards, no jungle, lane prison, death penalty, no farm.',
                'games' => ['MOBA'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Wards', 'is_default' => false],
                    ['name' => 'No Jungle Camps', 'is_default' => false],
                    ['name' => 'Starting Lane Only', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false], // Counter: max 1
                    ['name' => 'No Last Hitting', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 2. MOBA: Passive Farmer (Easy)
            [
                'name' => 'MOBA: Passive Farmer',
                'description' => 'No aggressive farming. Can\'t last hit, can\'t jungle, stay in lane. Viewers add: no farm, no jungle, lane prison, death, no wards, free pass.',
                'games' => ['MOBA'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Last Hitting', 'is_default' => false],
                    ['name' => 'No Jungle Camps', 'is_default' => false],
                    ['name' => 'Starting Lane Only', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false], // Counter: max 1
                    ['name' => 'No Wards', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 3. MOBA: Vision Denial (Medium)
            [
                'name' => 'MOBA: Vision Denial',
                'description' => 'Play blind. No vision for extended time. Viewers add: no wards (court), no ultimate, no jungle, death, lane prison, free pass.',
                'games' => ['MOBA'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Wards (Court)', 'is_default' => false],
                    ['name' => 'No Ultimate Ability', 'is_default' => false],
                    ['name' => 'No Jungle Camps', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false], // Counter: max 1
                    ['name' => 'Starting Lane Only', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 4. MOBA: Lane Prison (Medium)
            [
                'name' => 'MOBA: Lane Prison',
                'description' => 'Locked in lane for extended time. No roaming allowed. Viewers add: lane prison (court), no farm, no wards, death, no jungle, free pass.',
                'games' => ['MOBA'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'Starting Lane Only (Court)', 'is_default' => false],
                    ['name' => 'No Last Hitting', 'is_default' => false],
                    ['name' => 'No Wards', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false], // Counter: max 1
                    ['name' => 'No Jungle Camps', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 5. MOBA: No Ultimate Challenge (Hard)
            [
                'name' => 'MOBA: No Ultimate Challenge',
                'description' => 'No R ability for extended time. Basic abilities only. Viewers add: no ultimate (court), no farm (court), no wards, death, lane prison, free pass.',
                'games' => ['MOBA'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Ultimate Ability (Court)', 'is_default' => false],
                    ['name' => 'No Last Hitting (Court)', 'is_default' => false],
                    ['name' => 'No Wards', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false], // Counter: max 1
                    ['name' => 'Starting Lane Only', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 6. MOBA: Jungle Lockout (Medium)
            [
                'name' => 'MOBA: Jungle Lockout',
                'description' => 'Jungle off-limits for extended time. Lane gold only. Viewers add: no jungle (court), no farm, no wards, death, lane prison, free pass.',
                'games' => ['MOBA'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Jungle Camps (Court)', 'is_default' => false],
                    ['name' => 'No Last Hitting', 'is_default' => false],
                    ['name' => 'No Wards', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false], // Counter: max 1
                    ['name' => 'Starting Lane Only', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 7. MOBA: Blind Fight (Hard)
            [
                'name' => 'MOBA: Blind Fight',
                'description' => 'Extended no vision + no ultimate. Maximum difficulty. Viewers add: no wards (court), no ultimate (court), no farm, death, no jungle, free pass.',
                'games' => ['MOBA'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Wards (Court)', 'is_default' => false],
                    ['name' => 'No Ultimate Ability (Court)', 'is_default' => false],
                    ['name' => 'No Last Hitting', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false], // Counter: max 1
                    ['name' => 'No Jungle Camps', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 8. MOBA: Full Restriction (Expert)
            [
                'name' => 'MOBA: Full Restriction',
                'description' => 'Triple court rules. No ultimate, no vision, lane locked. Ultimate challenge. Viewers add: no ultimate (court), no wards (court), lane prison (court), death, no farm, free pass.',
                'games' => ['MOBA'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Ultimate Ability (Court)', 'is_default' => false],
                    ['name' => 'No Wards (Court)', 'is_default' => false],
                    ['name' => 'Starting Lane Only (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false], // Counter: max 1
                    ['name' => 'No Last Hitting', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 9. MOBA: Pure Chaos (Easy)
            [
                'name' => 'MOBA: Pure Chaos',
                'description' => 'All basic restrictions active. Pure viewer control. Viewers add: no farm, no wards, no ultimate, no jungle, lane prison, death, free pass.',
                'games' => ['MOBA'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Last Hitting', 'is_default' => false],
                    ['name' => 'No Wards', 'is_default' => false],
                    ['name' => 'No Ultimate Ability', 'is_default' => false],
                    ['name' => 'No Jungle Camps', 'is_default' => false],
                    ['name' => 'Starting Lane Only', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false], // Counter: max 1
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 10. MOBA: Viewer's Choice (Medium)
            [
                'name' => 'MOBA: Viewer\'s Choice',
                'description' => 'Mix of basic and court rules. Balanced chaos. Viewers add: no wards, no ultimate, no farm (court), lane prison, death, free pass.',
                'games' => ['MOBA'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Wards', 'is_default' => false],
                    ['name' => 'No Ultimate Ability', 'is_default' => false],
                    ['name' => 'No Last Hitting (Court)', 'is_default' => false],
                    ['name' => 'Starting Lane Only', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false], // Counter: max 1
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // ========================================
            // STRATEGY CATEGORY - UNIVERSAL RULESETS (10 TOTAL)
            // ========================================

            // 1. Strategy: Economic Only (Easy)
            [
                'name' => 'Strategy: Economic Only',
                'description' => 'Focus on economy, limited military. Viewers add: no units, no defenses, no tech, no building, free pass.',
                'games' => ['Strategy'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Unit Production', 'is_default' => false],
                    ['name' => 'No Defensive Structures', 'is_default' => false],
                    ['name' => 'No Tech Upgrades', 'is_default' => false],
                    ['name' => 'No Building Construction', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 2. Strategy: No Expansion (Easy)
            [
                'name' => 'Strategy: No Expansion',
                'description' => 'Starting base only. No expansion buildings. Viewers add: no building, no units, no tech, no defenses, free pass.',
                'games' => ['Strategy'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Building Construction', 'is_default' => false],
                    ['name' => 'No Unit Production', 'is_default' => false],
                    ['name' => 'No Tech Upgrades', 'is_default' => false],
                    ['name' => 'No Defensive Structures', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 3. Strategy: Starting Army Only (Medium)
            [
                'name' => 'Strategy: Starting Army Only',
                'description' => 'Use only starting units for extended time. Every loss is permanent. Viewers add: starting units (court), no tech, no building, no defenses, free pass.',
                'games' => ['Strategy'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'Starting Units Only (Court)', 'is_default' => false],
                    ['name' => 'No Tech Upgrades', 'is_default' => false],
                    ['name' => 'No Building Construction', 'is_default' => false],
                    ['name' => 'No Defensive Structures', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 4. Strategy: No Defense Allowed (Medium)
            [
                'name' => 'Strategy: No Defense Allowed',
                'description' => 'Pure offense for extended time. No defensive structures. Viewers add: no defenses (court), no units, no tech, no building, free pass.',
                'games' => ['Strategy'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Defensive Structures (Court)', 'is_default' => false],
                    ['name' => 'No Unit Production', 'is_default' => false],
                    ['name' => 'No Tech Upgrades', 'is_default' => false],
                    ['name' => 'No Building Construction', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 5. Strategy: Tech Freeze (Hard)
            [
                'name' => 'Strategy: Tech Freeze',
                'description' => 'Extended tech lockout. No research, tier 1 only. Viewers add: no tech (court), no units (court), no building, no defenses, free pass.',
                'games' => ['Strategy'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Tech Upgrades (Court)', 'is_default' => false],
                    ['name' => 'No Unit Production (Court)', 'is_default' => false],
                    ['name' => 'No Building Construction', 'is_default' => false],
                    ['name' => 'No Defensive Structures', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 6. Strategy: No Production (Hard)
            [
                'name' => 'Strategy: No Production',
                'description' => 'Extended unit production lockout. Starting army must last. Viewers add: no units (court), no building (court), no tech, no defenses, free pass.',
                'games' => ['Strategy'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Unit Production (Court)', 'is_default' => false],
                    ['name' => 'No Building Construction (Court)', 'is_default' => false],
                    ['name' => 'No Tech Upgrades', 'is_default' => false],
                    ['name' => 'No Defensive Structures', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 7. Strategy: Minimalist Commander (Expert)
            [
                'name' => 'Strategy: Minimalist Commander',
                'description' => 'Triple court restrictions. Maximum difficulty. Viewers add: no building (court), no units (court), no tech (court), no defenses, free pass.',
                'games' => ['Strategy'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Building Construction (Court)', 'is_default' => false],
                    ['name' => 'No Unit Production (Court)', 'is_default' => false],
                    ['name' => 'No Tech Upgrades (Court)', 'is_default' => false],
                    ['name' => 'No Defensive Structures', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 8. Strategy: Pure Starting Force (Hard)
            [
                'name' => 'Strategy: Pure Starting Force',
                'description' => 'Extended starting units only + no expansion. Viewers add: starting units (court), no building (court), no tech, no defenses, free pass.',
                'games' => ['Strategy'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'Starting Units Only (Court)', 'is_default' => false],
                    ['name' => 'No Building Construction (Court)', 'is_default' => false],
                    ['name' => 'No Tech Upgrades', 'is_default' => false],
                    ['name' => 'No Defensive Structures', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 9. Strategy: Pure Chaos (Easy)
            [
                'name' => 'Strategy: Pure Chaos',
                'description' => 'All restrictions available. Pure viewer control. Viewers add: no building, no units, no defenses, no tech, starting units only, free pass.',
                'games' => ['Strategy'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Building Construction', 'is_default' => false],
                    ['name' => 'No Unit Production', 'is_default' => false],
                    ['name' => 'No Defensive Structures', 'is_default' => false],
                    ['name' => 'No Tech Upgrades', 'is_default' => false],
                    ['name' => 'Starting Units Only', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 10. Strategy: Viewer's Command (Medium)
            [
                'name' => 'Strategy: Viewer\'s Command',
                'description' => 'Mix of basic and court rules. Balanced challenge. Viewers add: no units, no tech (court), no building, no defenses, free pass.',
                'games' => ['Strategy'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Unit Production', 'is_default' => false],
                    ['name' => 'No Tech Upgrades (Court)', 'is_default' => false],
                    ['name' => 'No Building Construction', 'is_default' => false],
                    ['name' => 'No Defensive Structures', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // ========================================
            // SURVIVAL CATEGORY - UNIVERSAL RULESETS (10 TOTAL)
            // ========================================

            // 1. Survival: Scavenger Only (Easy)
            [
                'name' => 'Survival: Scavenger Only',
                'description' => 'Pure scavenging. No crafting, no building, no gathering. Find everything. Viewers add: no craft, no build, no gather, no food, no water, free pass.',
                'games' => ['Survival'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Crafting Items', 'is_default' => false],
                    ['name' => 'No Building Shelter', 'is_default' => false],
                    ['name' => 'No Resource Gathering', 'is_default' => false],
                    ['name' => 'No Eating Food', 'is_default' => false],
                    ['name' => 'No Drinking Water', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 2. Survival: No Crafting (Medium)
            [
                'name' => 'Survival: No Crafting',
                'description' => 'Extended crafting lockout. Find or scavenge only. Viewers add: no craft (court), no build, no gather, no food, free pass.',
                'games' => ['Survival'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Crafting Items (Court)', 'is_default' => false],
                    ['name' => 'No Building Shelter', 'is_default' => false],
                    ['name' => 'No Resource Gathering', 'is_default' => false],
                    ['name' => 'No Eating Food', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 3. Survival: Nomadic Life (Medium)
            [
                'name' => 'Survival: Nomadic Life',
                'description' => 'Extended no shelter. Pure nomadic survival. Viewers add: no build (court), no craft, no gather, no water, free pass.',
                'games' => ['Survival'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Building Shelter (Court)', 'is_default' => false],
                    ['name' => 'No Crafting Items', 'is_default' => false],
                    ['name' => 'No Resource Gathering', 'is_default' => false],
                    ['name' => 'No Drinking Water', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 4. Survival: Hunger Strike (Hard)
            [
                'name' => 'Survival: Hunger Strike',
                'description' => 'Permanent no healing + extended starvation. Viewers add: no food (court), no water, no craft, no build, heal counter, free pass.',
                'games' => ['Survival'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'name' => 'No Healing', 'is_default' => true],
                    ['name' => 'No Eating Food (Court)', 'is_default' => false],
                    ['name' => 'No Drinking Water', 'is_default' => false],
                    ['name' => 'No Crafting Items', 'is_default' => false],
                    ['name' => 'No Building Shelter', 'is_default' => false],
                    ['name' => 'Use Healing Item', 'is_default' => false], // Counter
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 5. Survival: Resource Lockout (Hard)
            [
                'name' => 'Survival: Resource Lockout',
                'description' => 'Extended no gathering or crafting. Pure scavenging. Viewers add: no gather (court), no craft (court), no build, no food, free pass.',
                'games' => ['Survival'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Resource Gathering (Court)', 'is_default' => false],
                    ['name' => 'No Crafting Items (Court)', 'is_default' => false],
                    ['name' => 'No Building Shelter', 'is_default' => false],
                    ['name' => 'No Eating Food', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 6. Survival: Thirst Trap (Medium)
            [
                'name' => 'Survival: Thirst Trap',
                'description' => 'Extended thirst. No drinking water. Viewers add: no water (court), no food, no craft, no gather, free pass.',
                'games' => ['Survival'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Drinking Water (Court)', 'is_default' => false],
                    ['name' => 'No Eating Food', 'is_default' => false],
                    ['name' => 'No Crafting Items', 'is_default' => false],
                    ['name' => 'No Resource Gathering', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 7. Survival: Hardcore Minimalist (Expert)
            [
                'name' => 'Survival: Hardcore Minimalist',
                'description' => 'Extended no crafting or shelter. Maximum difficulty. Viewers add: no craft (court), no build (court), no gather, no food, walking only, free pass.',
                'games' => ['Survival'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Crafting Items (Court)', 'is_default' => false],
                    ['name' => 'No Building Shelter (Court)', 'is_default' => false],
                    ['name' => 'No Resource Gathering', 'is_default' => false],
                    ['name' => 'No Eating Food', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false], // Basic rule now
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 8. Survival: No Shelter Challenge (Hard)
            [
                'name' => 'Survival: No Shelter Challenge',
                'description' => 'Extended no shelter or gathering. Nomadic hardcore. Viewers add: no build (court), no gather (court), no craft, no water, free pass.',
                'games' => ['Survival'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Building Shelter (Court)', 'is_default' => false],
                    ['name' => 'No Resource Gathering (Court)', 'is_default' => false],
                    ['name' => 'No Crafting Items', 'is_default' => false],
                    ['name' => 'No Drinking Water', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 9. Survival: Pure Chaos (Easy)
            [
                'name' => 'Survival: Pure Chaos',
                'description' => 'All basic restrictions available. Pure viewer control. Viewers add: no craft, no build, no gather, no food, no water, free pass.',
                'games' => ['Survival'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Crafting Items', 'is_default' => false],
                    ['name' => 'No Building Shelter', 'is_default' => false],
                    ['name' => 'No Resource Gathering', 'is_default' => false],
                    ['name' => 'No Eating Food', 'is_default' => false],
                    ['name' => 'No Drinking Water', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 10. Survival: Viewer's Wilderness (Medium)
            [
                'name' => 'Survival: Viewer\'s Wilderness',
                'description' => 'Mix of basic and court rules. Balanced survival. Viewers add: no food, no craft (court), no build, no gather, free pass.',
                'games' => ['Survival'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Eating Food', 'is_default' => false],
                    ['name' => 'No Crafting Items (Court)', 'is_default' => false],
                    ['name' => 'No Building Shelter', 'is_default' => false],
                    ['name' => 'No Resource Gathering', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // =====================================================
            // MULTIPLAYER SHOOTER RULESETS (13)
            // =====================================================

            // 1. Shooter MP: Pistol Party (Easy - 1 Permanent)
            [
                'name' => 'Shooter MP: Pistol Party',
                'description' => 'Pistols only with movement chaos. Viewers add: walking, no jump, no grenades (court), die (3x), no reload, free pass.',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_PISTOL_ONLY, 'is_default' => true],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'No Grenades (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'No Reloading', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 2. Shooter MP: Hipfire Hero (Easy - 1 Permanent)
            [
                'name' => 'Shooter MP: Hipfire Hero',
                'description' => 'No aiming down sights. Raw hipfire skill. Viewers add: no sprint, no crouch, no jump, die (3x), no reload, free pass.',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ADS, 'is_default' => true],
                    ['name' => 'No Sprint', 'is_default' => false],
                    ['name' => 'No Crouching', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'No Reloading', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 3. Shooter MP: Melee Mayhem (Easy - 1 Permanent)
            [
                'name' => 'Shooter MP: Melee Mayhem',
                'description' => 'Melee weapons only. Close quarters combat. Viewers add: walking, no jump, no crouch, die (5x), no grenades, free pass.',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_MELEE_ONLY, 'is_default' => true],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'No Crouching', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'No Grenades', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 4. Shooter MP: Sniper School (Medium - 1 Permanent)
            [
                'name' => 'Shooter MP: Sniper School',
                'description' => 'Sniper rifles only. Long range precision. Viewers add: no crouch, no ADS (court), walking (court), die (2x), no reload (court), free pass (court).',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'Sniper Only', 'is_default' => true],
                    ['name' => 'No Crouching', 'is_default' => false],
                    ['name' => 'No ADS (Court)', 'is_default' => false],
                    ['name' => 'Walking Only (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'No Reloading (Court)', 'is_default' => false],
                    ['name' => 'Free Pass (Court)', 'is_default' => false],
                ],
            ],

            // 5. Shooter MP: ADS Addict (Medium - 1 Permanent)
            [
                'name' => 'Shooter MP: ADS Addict',
                'description' => 'Must always aim down sights. Slow but accurate. Viewers add: walking, no jump, no sprint, die (3x), no reload, free pass.',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'ADS Only', 'is_default' => true],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'No Sprint', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'No Reloading', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 6. Shooter MP: No Scope Challenge (Medium - 2 Permanent)
            [
                'name' => 'Shooter MP: No Scope Challenge',
                'description' => 'Hipfire only, stay grounded. Viewers add: no sprint, no crouch, die (3x), no reload, free pass.',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ADS, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_JUMPING, 'is_default' => true],
                    ['name' => 'No Sprint', 'is_default' => false],
                    ['name' => 'No Crouching', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'No Reloading', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 7. Shooter MP: Crouch Master (Hard - 2 Permanent)
            [
                'name' => 'Shooter MP: Crouch Master',
                'description' => 'Crouch only, no jumping. Slow tactical gameplay. Viewers add: no grenades (court), no ADS (court), die (2x), no reload (court), free pass (court).',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'Crouch Only', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_JUMPING, 'is_default' => true],
                    ['name' => 'No Grenades (Court)', 'is_default' => false],
                    ['name' => 'No ADS (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'No Reloading (Court)', 'is_default' => false],
                    ['name' => 'Free Pass (Court)', 'is_default' => false],
                ],
            ],

            // 8. Shooter MP: Explosive Expert (Hard - 2 Permanent)
            [
                'name' => 'Shooter MP: Explosive Expert',
                'description' => 'Stay grounded, no aerial movement. Viewers add: pistol (court), no ADS (court), die (2x), no reload (court), free pass (court).',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_JUMPING, 'is_default' => true],
                    ['name' => 'No Crouching', 'is_default' => true],
                    ['name' => 'Pistol Only (Court)', 'is_default' => false],
                    ['name' => 'No ADS (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'No Reloading (Court)', 'is_default' => false],
                    ['name' => 'Free Pass (Court)', 'is_default' => false],
                ],
            ],

            // 9. Shooter MP: Iron Sights Only (Hard - 2 Permanent)
            [
                'name' => 'Shooter MP: Iron Sights Only',
                'description' => 'ADS only, no jumping. Precision gameplay. Viewers add: pistol (court), no sprint (court), die (2x), no reload (court), free pass (court).',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'ADS Only', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_JUMPING, 'is_default' => true],
                    ['name' => 'Pistol Only (Court)', 'is_default' => false],
                    ['name' => 'No Sprint (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'No Reloading (Court)', 'is_default' => false],
                    ['name' => 'Free Pass (Court)', 'is_default' => false],
                ],
            ],

            // 10. Shooter MP: Sniper's Nightmare (Hard - 2 Permanent)
            [
                'name' => 'Shooter MP: Sniper\'s Nightmare',
                'description' => 'Hipfire only, no crouching. No accuracy bonus. Viewers add: no grenades (court), no sprint (court), die (1x), no reload (court), free pass (court).',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ADS, 'is_default' => true],
                    ['name' => 'No Crouching', 'is_default' => true],
                    ['name' => 'No Grenades (Court)', 'is_default' => false],
                    ['name' => 'No Sprint (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'No Reloading (Court)', 'is_default' => false],
                    ['name' => 'Free Pass (Court)', 'is_default' => false],
                ],
            ],

            // 11. Shooter MP: Primary Priority (Easy - 1 Permanent)
            [
                'name' => 'Shooter MP: Primary Priority',
                'description' => 'No sidearm, primary weapon only. Ammo management critical. Viewers add: no sprint, no jump, no grenades (court), die (3x), no reload, free pass.',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SIDEARM, 'is_default' => true],
                    ['name' => 'No Sprint', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'No Grenades (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'No Reloading', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 12. Shooter MP: Long Range Only (Medium - 2 Permanent)
            [
                'name' => 'Shooter MP: Long Range Only',
                'description' => 'No sidearm, no melee. Keep distance. Viewers add: no sprint, no crouch, die (2x), no reload (court), free pass (court).',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SIDEARM, 'is_default' => true],
                    ['name' => 'No Melee', 'is_default' => true],
                    ['name' => 'No Sprint', 'is_default' => false],
                    ['name' => 'No Crouching', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'No Reloading (Court)', 'is_default' => false],
                    ['name' => 'Free Pass (Court)', 'is_default' => false],
                ],
            ],

            // 13. Shooter MP: Sniper's Code (Hard - 2 Permanent)
            [
                'name' => 'Shooter MP: Sniper\'s Code',
                'description' => 'No sidearm, ADS only. Precision is everything. Viewers add: no sprint (court), no jump (court), die (2x), no reload (court), free pass (court).',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SIDEARM, 'is_default' => true],
                    ['name' => 'ADS Only', 'is_default' => true],
                    ['name' => 'No Sprint (Court)', 'is_default' => false],
                    ['name' => 'No Jumping (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'No Reloading (Court)', 'is_default' => false],
                    ['name' => 'Free Pass (Court)', 'is_default' => false],
                ],
            ],

            // =====================================================
            // SINGLEPLAYER SHOOTER RULESETS (10)
            // =====================================================

            // 1. Shooter SP: Weapon Swap Challenge (Easy - 1 Permanent)
            [
                'name' => 'Shooter SP: Weapon Swap Challenge',
                'description' => 'No reloading permanent. Limited reloads. Viewers add: pistol only, sniper only, die (5x), reload weapon (5x max), crouch (30x), free pass.',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_RELOADING, 'is_default' => true],
                    ['name' => 'Pistol Only', 'is_default' => false],
                    ['name' => 'Sniper Only', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Reload Weapon', 'is_default' => false],
                    ['name' => 'Crouch', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 2. Shooter SP: Hipfire Story (Easy - 1 Permanent)
            [
                'name' => 'Shooter SP: Hipfire Story',
                'description' => 'Hipfire only. No ADS precision. Viewers add: no sprint, no crouch, no jump, die (5x), melee attack (25x), no melee (court), free pass.',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ADS, 'is_default' => true],
                    ['name' => 'No Sprint', 'is_default' => false],
                    ['name' => 'No Crouching', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Melee Attack', 'is_default' => false],
                    ['name' => 'No Melee (Court)', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 3. Shooter SP: Melee Master (Easy - 0 Permanent)
            [
                'name' => 'Shooter SP: Melee Master',
                'description' => 'Time-based melee challenge. Viewers add: melee only, walking only, no jump, die (8x), crouch (40x), free pass.',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'Melee Only', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Crouch', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 4. Shooter SP: Resource Crisis (Easy - 0 Permanent)
            [
                'name' => 'Shooter SP: Resource Crisis',
                'description' => 'Resource scarcity challenge. Viewers add: no healing, no ammo pickup, no reload (court), die (10x), melee attack (30x), free pass.',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'No Healing Item', 'is_default' => false],
                    ['name' => 'No Ammo Pickup', 'is_default' => false],
                    ['name' => 'No Reloading (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Melee Attack', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 5. Shooter SP: Sniper Campaign (Medium - 0 Permanent)
            [
                'name' => 'Shooter SP: Sniper Campaign',
                'description' => 'Long range precision. Viewers add: sniper only (court), no crouch, walking (court), die (3x), crouch (40x), free pass (court).',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'Sniper Only (Court)', 'is_default' => false],
                    ['name' => 'No Crouching', 'is_default' => false],
                    ['name' => 'Walking Only (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Crouch', 'is_default' => false],
                    ['name' => 'Free Pass (Court)', 'is_default' => false],
                ],
            ],

            // 6. Shooter SP: Survival Mode (Medium - 1 Permanent)
            [
                'name' => 'Shooter SP: Survival Mode',
                'description' => 'Hipfire survival. Viewers add: no healing, no sprint, no reload (court), die (5x), melee attack (20x), no melee (court), free pass.',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ADS, 'is_default' => true],
                    ['name' => 'No Healing Item', 'is_default' => false],
                    ['name' => 'No Sprint', 'is_default' => false],
                    ['name' => 'No Reloading (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Melee Attack', 'is_default' => false],
                    ['name' => 'No Melee (Court)', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 7. Shooter SP: No Scope Challenge (Hard - 3 Permanent)
            [
                'name' => 'Shooter SP: No Scope Challenge',
                'description' => 'Hipfire only, no jumping, limited reloads. Viewers add: no healing (court), no sprint, die (3x), reload weapon (5x max), free pass (court).',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ADS, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_JUMPING, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_RELOADING, 'is_default' => true],
                    ['name' => 'No Healing Item (Court)', 'is_default' => false],
                    ['name' => 'No Sprint', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Reload Weapon', 'is_default' => false],
                    ['name' => 'Free Pass (Court)', 'is_default' => false],
                ],
            ],

            // 8. Shooter SP: Iron Man Mode (Hard - 2 Permanent)
            [
                'name' => 'Shooter SP: Iron Man Mode',
                'description' => 'No saves, no jumping. Ultimate survival. Viewers add: no healing (court), walking (court), die (2x), no reload (court), free pass (court).',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_JUMPING, 'is_default' => true],
                    ['name' => 'No Healing Item (Court)', 'is_default' => false],
                    ['name' => 'Walking Only (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'No Reloading (Court)', 'is_default' => false],
                    ['name' => 'Free Pass (Court)', 'is_default' => false],
                ],
            ],

            // 9. Shooter SP: Pistol Survivor (Hard - 2 Permanent)
            [
                'name' => 'Shooter SP: Pistol Survivor',
                'description' => 'No saves, limited reloads. Pistol mastery required. Viewers add: pistol (court), no healing (court), walking (court), die (2x), reload weapon (5x max), free pass (court).',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_RELOADING, 'is_default' => true],
                    ['name' => 'Pistol Only (Court)', 'is_default' => false],
                    ['name' => 'No Healing Item (Court)', 'is_default' => false],
                    ['name' => 'Walking Only (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Reload Weapon', 'is_default' => false],
                    ['name' => 'Free Pass (Court)', 'is_default' => false],
                ],
            ],

            // 10. Shooter SP: Ultimate Challenge (Hard - 2 Permanent)
            [
                'name' => 'Shooter SP: Ultimate Challenge',
                'description' => 'No ammo pickup, no jumping. Brutal scarcity. Viewers add: no healing (court), walking (court), die (1x), no reload (court), free pass (court).',
                'games' => ['Shooter'], // Category-universal ruleset
                'rules' => [
                    ['reference' => 'rule_no_ammo_pickup', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_JUMPING, 'is_default' => true],
                    ['name' => 'No Healing Item (Court)', 'is_default' => false],
                    ['name' => 'Walking Only (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'No Reloading (Court)', 'is_default' => false],
                    ['name' => 'Free Pass (Court)', 'is_default' => false],
                ],
            ],

            // =====================================================
            // FIGHTING GAME RULESETS (10)
            // =====================================================

            // 1. Fighting: No Defense (Easy - 1 Permanent)
            [
                'name' => 'Fighting: No Defense',
                'description' => 'No blocking allowed. Pure offense or dodge. Viewers choose: get hit (1x) or free pass.',
                'games' => ['Fighting'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_BLOCKING, 'is_default' => true],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 2. Fighting: Basic Attacks Only (Easy - 1 Permanent)
            [
                'name' => 'Fighting: Basic Attacks Only',
                'description' => 'No special moves. Fundamentals only. Viewers choose: get hit (1x) or free pass.',
                'games' => ['Fighting'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SPECIAL_MOVES, 'is_default' => true],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 3. Fighting: Ground Game (Easy - 1 Permanent)
            [
                'name' => 'Fighting: Ground Game',
                'description' => 'No jumping. Footsies only. Viewers choose: get hit (1x) or free pass.',
                'games' => ['Fighting'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_JUMPING, 'is_default' => true],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 4. Fighting: No Supers (Easy - 1 Permanent)
            [
                'name' => 'Fighting: No Supers',
                'description' => 'No super moves. Win without meter. Viewers choose: get hit (1x) or free pass.',
                'games' => ['Fighting'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SUPER_MOVES, 'is_default' => true],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 5. Fighting: Light Attacks Only (Easy - 1 Permanent)
            [
                'name' => 'Fighting: Light Attacks Only',
                'description' => 'Only light attacks. Quick jabs and combos. Viewers choose: get hit (1x) or free pass.',
                'games' => ['Fighting'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_LIGHT_ATTACKS_ONLY, 'is_default' => true],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 6. Fighting: Aggressive Footsies (Medium - 2 Permanent)
            [
                'name' => 'Fighting: Aggressive Footsies',
                'description' => 'No blocking, no jumping. Ground offense only. Viewers choose: get hit (1x) or free pass.',
                'games' => ['Fighting'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_BLOCKING, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_JUMPING, 'is_default' => true],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 7. Fighting: Pure Fundamentals (Medium - 2 Permanent)
            [
                'name' => 'Fighting: Pure Fundamentals',
                'description' => 'No special moves, no supers. Back to basics. Viewers choose: get hit (1x) or free pass.',
                'games' => ['Fighting'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SPECIAL_MOVES, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SUPER_MOVES, 'is_default' => true],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 8. Fighting: No Grabs Allowed (Medium - 2 Permanent)
            [
                'name' => 'Fighting: No Grabs Allowed',
                'description' => 'No blocking, no grabs. Pure striking. Viewers choose: get hit (1x) or free pass.',
                'games' => ['Fighting'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_BLOCKING, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_GRABS, 'is_default' => true],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 9. Fighting: Weak Offense (Hard - 3 Permanent)
            [
                'name' => 'Fighting: Weak Offense',
                'description' => 'Light attacks only, no blocking, no jumping. Brutal handicap. Viewers choose: get hit (1x) or free pass.',
                'games' => ['Fighting'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_LIGHT_ATTACKS_ONLY, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_BLOCKING, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_JUMPING, 'is_default' => true],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 10. Fighting: Ultimate Handicap (Hard - 3 Permanent)
            [
                'name' => 'Fighting: Ultimate Handicap',
                'description' => 'No blocking, no special moves, no supers. Maximum difficulty. Viewers choose: get hit (1x) or free pass.',
                'games' => ['Fighting'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_BLOCKING, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SPECIAL_MOVES, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SUPER_MOVES, 'is_default' => true],
                    ['name' => 'Get Hit by Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // =====================================================
            // BATTLE ROYALE RULESETS (10)
            // =====================================================

            // 1. BR: Pistol Royale (Easy - 1 Permanent)
            [
                'name' => 'BR: Pistol Royale',
                'description' => 'Pistols only. Viewers add: walking, no jump, no grenades (court), no healing, free pass.',
                'games' => ['Battle Royale'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_PISTOL_ONLY, 'is_default' => true],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'No Grenades (Court)', 'is_default' => false],
                    ['name' => 'No Healing Item', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 2. BR: Hipfire Hero (Easy - 1 Permanent)
            [
                'name' => 'BR: Hipfire Hero',
                'description' => 'Hipfire only. No ADS. Viewers add: no sprint, no crouch, no jump, no healing, free pass.',
                'games' => ['Battle Royale'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ADS, 'is_default' => true],
                    ['name' => 'No Sprint', 'is_default' => false],
                    ['name' => 'No Crouching', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'No Healing Item', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 3. BR: Melee Mayhem (Easy - 1 Permanent)
            [
                'name' => 'BR: Melee Mayhem',
                'description' => 'Melee only. Close combat. Viewers add: walking, no jump, no healing, no shield, free pass.',
                'games' => ['Battle Royale'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_MELEE_ONLY, 'is_default' => true],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'No Healing Item', 'is_default' => false],
                    ['name' => 'No Shield Pickup', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 4. BR: Sniper Only (Easy - 1 Permanent)
            [
                'name' => 'BR: Sniper Only',
                'description' => 'Sniper rifles only. Long range precision. Viewers add: no crouch, walking (court), no ADS (court), no healing, free pass.',
                'games' => ['Battle Royale'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_SNIPER_ONLY, 'is_default' => true],
                    ['name' => 'No Crouching', 'is_default' => false],
                    ['name' => 'Walking Only (Court)', 'is_default' => false],
                    ['name' => 'No ADS (Court)', 'is_default' => false],
                    ['name' => 'No Healing Item', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 5. BR: ADS Addict (Easy - 1 Permanent)
            [
                'name' => 'BR: ADS Addict',
                'description' => 'ADS only. Always scoped. Viewers add: walking, no jump, no sprint, no healing, free pass.',
                'games' => ['Battle Royale'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'ADS Only', 'is_default' => true],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'No Sprint', 'is_default' => false],
                    ['name' => 'No Healing Item', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 6. BR: No Scope Challenge (Medium - 2 Permanent)
            [
                'name' => 'BR: No Scope Challenge',
                'description' => 'Hipfire only, no jumping. Ground combat. Viewers add: no sprint, no crouch, no healing (court), free pass.',
                'games' => ['Battle Royale'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ADS, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_JUMPING, 'is_default' => true],
                    ['name' => 'No Sprint', 'is_default' => false],
                    ['name' => 'No Crouching', 'is_default' => false],
                    ['name' => 'No Healing Item (Court)', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 7. BR: Iron Sights Only (Medium - 2 Permanent)
            [
                'name' => 'BR: Iron Sights Only',
                'description' => 'ADS only, no jumping. Scoped gameplay. Viewers add: no sprint, no healing (court), no shield, free pass (court).',
                'games' => ['Battle Royale'], // Category-universal ruleset
                'rules' => [
                    ['name' => 'ADS Only', 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_JUMPING, 'is_default' => true],
                    ['name' => 'No Sprint', 'is_default' => false],
                    ['name' => 'No Healing Item (Court)', 'is_default' => false],
                    ['name' => 'No Shield Pickup', 'is_default' => false],
                    ['name' => 'Free Pass (Court)', 'is_default' => false],
                ],
            ],

            // 8. BR: No Sidearm (Medium - 2 Permanent)
            [
                'name' => 'BR: No Sidearm',
                'description' => 'No sidearm, no jumping. Primary weapon only. Viewers add: no healing, no shield, no grenades (court), free pass.',
                'games' => ['Battle Royale'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SIDEARM, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_JUMPING, 'is_default' => true],
                    ['name' => 'No Healing Item', 'is_default' => false],
                    ['name' => 'No Shield Pickup', 'is_default' => false],
                    ['name' => 'No Grenades (Court)', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 9. BR: Pistol Survivor (Hard - 3 Permanent)
            [
                'name' => 'BR: Pistol Survivor',
                'description' => 'Pistol only, hipfire only, no jumping. Brutal handicap. Viewers add: no healing (court), no shield (court), walking (court), free pass (court).',
                'games' => ['Battle Royale'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_PISTOL_ONLY, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ADS, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_JUMPING, 'is_default' => true],
                    ['name' => 'No Healing Item (Court)', 'is_default' => false],
                    ['name' => 'No Shield (Court)', 'is_default' => false],
                    ['name' => 'Walking Only (Court)', 'is_default' => false],
                    ['name' => 'Free Pass (Court)', 'is_default' => false],
                ],
            ],

            // 10. BR: Ultimate Handicap (Hard - 3 Permanent)
            [
                'name' => 'BR: Ultimate Handicap',
                'description' => 'Hipfire only, no grenades, no crouching. Maximum difficulty. Viewers add: no healing (court), no shield (court), walking (court), free pass (court).',
                'games' => ['Battle Royale'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ADS, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_GRENADES, 'is_default' => true],
                    ['name' => 'No Crouching', 'is_default' => true],
                    ['name' => 'No Healing Item (Court)', 'is_default' => false],
                    ['name' => 'No Shield Pickup (Court)', 'is_default' => false],
                    ['name' => 'Walking Only (Court)', 'is_default' => false],
                    ['name' => 'Free Pass (Court)', 'is_default' => false],
                ],
            ],

            // =====================================================
            // METROIDVANIA RULESETS (10)
            // =====================================================

            // 1. Metroidvania: No Fast Travel (Easy - 1 Permanent)
            [
                'name' => 'Metroidvania: No Fast Travel',
                'description' => 'Cannot fast travel. Walk everywhere. Viewers add: walking, no jump, no dash, die (1x), free pass.',
                'games' => ['Metroidvania'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_FAST_TRAVEL, 'is_default' => true],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Jumping', 'is_default' => false],
                    ['name' => 'No Dash', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 2. Metroidvania: Minimalist Run (Easy - 1 Permanent)
            [
                'name' => 'Metroidvania: Minimalist Run',
                'description' => 'Starting equipment only. No upgrades. Viewers add: no healing, walking, no dash, die (1x), free pass.',
                'games' => ['Metroidvania'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_STARTING_EQUIPMENT_ONLY, 'is_default' => true],
                    ['name' => 'No Healing Item', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Dash', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 3. Metroidvania: No Save Challenge (Easy - 1 Permanent)
            [
                'name' => 'Metroidvania: No Save Challenge',
                'description' => 'Cannot save progress. Viewers add: no healing, no dash, no double jump, die (1x), free pass.',
                'games' => ['Metroidvania'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'is_default' => true],
                    ['name' => 'No Healing Item', 'is_default' => false],
                    ['name' => 'No Dash', 'is_default' => false],
                    ['name' => 'No Double Jump', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 4. Metroidvania: Explorer (Easy - 1 Permanent)
            [
                'name' => 'Metroidvania: Explorer',
                'description' => 'No dash ability. Careful navigation. Viewers add: walking, no double jump, no healing, die (1x), free pass.',
                'games' => ['Metroidvania'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_DASH, 'is_default' => true],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'No Double Jump', 'is_default' => false],
                    ['name' => 'No Healing Item', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 5. Metroidvania: No Healing (Easy - 1 Permanent)
            [
                'name' => 'Metroidvania: No Healing',
                'description' => 'Cannot heal. Viewers add: no dash, walking, starting equipment, die (1x), free pass.',
                'games' => ['Metroidvania'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'is_default' => true],
                    ['name' => 'No Dash', 'is_default' => false],
                    ['name' => 'Walking Only', 'is_default' => false],
                    ['name' => 'Starting Equipment Only', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 6. Metroidvania: Hardcore Explorer (Medium - 2 Permanent)
            [
                'name' => 'Metroidvania: Hardcore Explorer',
                'description' => 'No fast travel, no saving. Ultimate exploration. Viewers add: no healing, no dash, die (1x), free pass.',
                'games' => ['Metroidvania'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_FAST_TRAVEL, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'is_default' => true],
                    ['name' => 'No Healing Item', 'is_default' => false],
                    ['name' => 'No Dash', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 7. Metroidvania: Base Kit Challenge (Medium - 2 Permanent)
            [
                'name' => 'Metroidvania: Base Kit Challenge',
                'description' => 'Starting equipment only, no fast travel. Back to basics. Viewers add: no dash, walking (court), die (1x), free pass.',
                'games' => ['Metroidvania'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_STARTING_EQUIPMENT_ONLY, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_FAST_TRAVEL, 'is_default' => true],
                    ['name' => 'No Dash', 'is_default' => false],
                    ['name' => 'Walking Only (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Free Pass', 'is_default' => false],
                ],
            ],

            // 8. Metroidvania: No Mobility (Medium - 2 Permanent)
            [
                'name' => 'Metroidvania: No Mobility',
                'description' => 'No dash, no double jump. Limited movement. Viewers add: walking (court), no healing (court), die (1x), free pass (court).',
                'games' => ['Metroidvania'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_DASH, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_DOUBLE_JUMP, 'is_default' => true],
                    ['name' => 'Walking Only (Court)', 'is_default' => false],
                    ['name' => 'No Healing Item (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Free Pass (Court)', 'is_default' => false],
                ],
            ],

            // 9. Metroidvania: Iron Man Mode (Hard - 3 Permanent)
            [
                'name' => 'Metroidvania: Iron Man Mode',
                'description' => 'No healing, no saving, no fast travel. Ultimate survival. Viewers add: no dash (court), die (1x), free pass (court).',
                'games' => ['Metroidvania'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_FAST_TRAVEL, 'is_default' => true],
                    ['name' => 'No Dash (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Free Pass (Court)', 'is_default' => false],
                ],
            ],

            // 10. Metroidvania: Ultimate Challenge (Hard - 3 Permanent)
            [
                'name' => 'Metroidvania: Ultimate Challenge',
                'description' => 'No dash, no double jump, no saving. Maximum difficulty. Viewers add: walking (court), no healing (court), die (1x), free pass (court).',
                'games' => ['Metroidvania'], // Category-universal ruleset
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_DASH, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_DOUBLE_JUMP, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'is_default' => true],
                    ['name' => 'Walking Only (Court)', 'is_default' => false],
                    ['name' => 'No Healing Item (Court)', 'is_default' => false],
                    ['name' => 'Die to Enemy', 'is_default' => false],
                    ['name' => 'Free Pass (Court)', 'is_default' => false],
                ],
            ],

            // =====================================================
            // GAME-SPECIFIC RULESETS (48)
            // =====================================================

            // === HADES (3) ===
            [
                'name' => 'Hades: No Boons Challenge',
                'description' => 'Cannot accept any boons from gods. Base damage only. Pure skill run.',
                'games' => ['Hades'],
                'rules' => [
                    ['name' => 'No Boons', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Hades: Fresh File',
                'description' => 'Cannot use Mirror of Night upgrades. Fresh start difficulty.',
                'games' => ['Hades'],
                'rules' => [
                    ['name' => 'No Mirror Upgrades', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Hades: Starting Weapon Only',
                'description' => 'No weapon upgrades. Base weapon stats throughout entire run.',
                'games' => ['Hades'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_STARTING_EQUIPMENT_ONLY, 'is_default' => true],
                ],
            ],

            // === HADES II (3) ===
            [
                'name' => 'Hades II: No Boons Challenge',
                'description' => 'Cannot accept any boons. Pure skill run.',
                'games' => ['Hades II (2024)'],
                'rules' => [
                    ['name' => 'No Boons', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Hades II: No Arcana Cards',
                'description' => 'Cannot use Arcana card buffs. Base stats only.',
                'games' => ['Hades II (2024)'],
                'rules' => [
                    ['name' => 'No Arcana Cards', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Hades II: Starting Weapon Only',
                'description' => 'No weapon upgrades. Default power level.',
                'games' => ['Hades II (2024)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_STARTING_EQUIPMENT_ONLY, 'is_default' => true],
                ],
            ],

            // === THE BINDING OF ISAAC (3) ===
            [
                'name' => 'Isaac: Base Damage Only',
                'description' => 'Cannot pick up damage-increasing items. Tears stat locked.',
                'games' => ['The Binding of Isaac'],
                'rules' => [
                    ['name' => 'No Damage Items', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Isaac: No Soul/Black Hearts',
                'description' => 'Can only use red hearts for health. No soul or black hearts.',
                'games' => ['The Binding of Isaac'],
                'rules' => [
                    ['name' => 'Red Hearts Only', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Isaac: Blindfolded Run',
                'description' => 'Cannot shoot tears. Bombs and orbitals only. Ultimate challenge.',
                'games' => ['The Binding of Isaac'],
                'rules' => [
                    ['name' => 'No Tears', 'is_default' => true],
                ],
            ],

            // === THE BINDING OF ISAAC: REBIRTH (3) ===
            [
                'name' => 'Isaac Rebirth: Base Damage Only',
                'description' => 'Cannot pick up damage-increasing items. Tears stat locked.',
                'games' => ['The Binding of Isaac: Rebirth (2014)'],
                'rules' => [
                    ['name' => 'No Damage Items', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Isaac Rebirth: No Soul/Black Hearts',
                'description' => 'Can only use red hearts for health. No soul or black hearts.',
                'games' => ['The Binding of Isaac: Rebirth (2014)'],
                'rules' => [
                    ['name' => 'Red Hearts Only', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Isaac Rebirth: Blindfolded Run',
                'description' => 'Cannot shoot tears. Bombs and orbitals only. Ultimate challenge.',
                'games' => ['The Binding of Isaac: Rebirth (2014)'],
                'rules' => [
                    ['name' => 'No Tears', 'is_default' => true],
                ],
            ],

            // === SLAY THE SPIRE (3) ===
            [
                'name' => 'Slay the Spire: Starter Deck Only',
                'description' => 'Cannot add cards to deck. Starting cards only. Minimalist extreme.',
                'games' => ['Slay the Spire (2019)'],
                'rules' => [
                    ['name' => 'No Card Pickups', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Slay the Spire: No Rare Cards',
                'description' => 'Cannot pick up rare cards. Commons and Uncommons only.',
                'games' => ['Slay the Spire (2019)'],
                'rules' => [
                    ['name' => 'No Rare Cards', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Slay the Spire: Minimalist Run',
                'description' => 'Must remove cards at every opportunity. Smallest deck possible.',
                'games' => ['Slay the Spire (2019)'],
                'rules' => [
                    ['name' => 'Remove Cards Always', 'is_default' => true],
                ],
            ],

            // === BALATRO (3) ===
            [
                'name' => 'Balatro: No Shop Challenge',
                'description' => 'Cannot buy from shop. Found items only. Economy challenge.',
                'games' => ['Balatro (2024)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SHOP, 'is_default' => true],
                ],
            ],
            [
                'name' => 'Balatro: Base Deck Only',
                'description' => 'Cannot add jokers or planet cards. Base deck power only.',
                'games' => ['Balatro (2024)'],
                'rules' => [
                    ['name' => 'No Jokers', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Balatro: One Hand Type',
                'description' => 'Can only score with one poker hand type. Pair only, Flush only, etc.',
                'games' => ['Balatro (2024)'],
                'rules' => [
                    ['name' => 'Single Hand Type', 'is_default' => true],
                ],
            ],

            // === ZELDA: OCARINA OF TIME (3) ===
            [
                'name' => 'Zelda OOT: 3 Heart Challenge',
                'description' => 'Cannot collect heart containers or pieces. Stay at 3 hearts.',
                'games' => ['The Legend of Zelda: Ocarina of Time (1998)'],
                'rules' => [
                    ['name' => '3 Hearts Only', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Zelda OOT: Swordless',
                'description' => 'Cannot use swords. Alternative weapons only. Creative combat.',
                'games' => ['The Legend of Zelda: Ocarina of Time (1998)'],
                'rules' => [
                    ['name' => 'No Sword', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Zelda OOT: No Bottle/Fairy',
                'description' => 'Cannot use bottles or fairies for healing. No safety net.',
                'games' => ['The Legend of Zelda: Ocarina of Time (1998)'],
                'rules' => [
                    ['name' => 'No Bottles', 'is_default' => true],
                ],
            ],

            // === ZELDA: BREATH OF THE WILD (3) ===
            [
                'name' => 'Zelda BOTW: 3 Heart Challenge',
                'description' => 'Cannot increase max hearts beyond starting 3. One-shot danger.',
                'games' => ['The Legend of Zelda: Breath of the Wild (2017)'],
                'rules' => [
                    ['name' => '3 Hearts Only', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Zelda BOTW: No Sheikah Slate',
                'description' => 'Cannot use Sheikah Slate abilities. No bombs, stasis, or magnesis.',
                'games' => ['The Legend of Zelda: Breath of the Wild (2017)'],
                'rules' => [
                    ['name' => 'No Sheikah Slate', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Zelda BOTW: No Healing Food',
                'description' => 'Cannot cook or eat healing food. Natural healing only.',
                'games' => ['The Legend of Zelda: Breath of the Wild (2017)'],
                'rules' => [
                    ['name' => 'No Healing Food', 'is_default' => true],
                ],
            ],

            // === ZELDA: TEARS OF THE KINGDOM (3) ===
            [
                'name' => 'Zelda TOTK: 3 Heart Challenge',
                'description' => 'Cannot increase max hearts. Stay at 3 hearts entire game.',
                'games' => ['The Legend of Zelda: Tears of the Kingdom (2023)'],
                'rules' => [
                    ['name' => '3 Hearts Only', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Zelda TOTK: No Fuse Ability',
                'description' => 'Cannot use fuse mechanic. Base weapons only.',
                'games' => ['The Legend of Zelda: Tears of the Kingdom (2023)'],
                'rules' => [
                    ['name' => 'No Fuse', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Zelda TOTK: No Healing Food',
                'description' => 'Cannot cook or eat healing food. Survival difficulty.',
                'games' => ['The Legend of Zelda: Tears of the Kingdom (2023)'],
                'rules' => [
                    ['name' => 'No Healing Food', 'is_default' => true],
                ],
            ],

            // === ZELDA: MAJORA'S MASK (3) ===
            [
                'name' => 'Zelda MM: 3 Heart Challenge',
                'description' => 'No heart containers or pieces. 3 hearts only.',
                'games' => ['The Legend of Zelda: Majora\'s Mask (2000)'],
                'rules' => [
                    ['name' => '3 Hearts Only', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Zelda MM: No Transformation Masks',
                'description' => 'Cannot use Deku, Goron, or Zora masks. Human Link only.',
                'games' => ['The Legend of Zelda: Majora\'s Mask (2000)'],
                'rules' => [
                    ['name' => 'No Transformation Masks', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Zelda MM: No Bottle/Fairy',
                'description' => 'No bottles or fairies. No healing safety net.',
                'games' => ['The Legend of Zelda: Majora\'s Mask (2000)'],
                'rules' => [
                    ['name' => 'No Bottles', 'is_default' => true],
                ],
            ],

            // === ZELDA: TWILIGHT PRINCESS (3) ===
            [
                'name' => 'Zelda TP: 3 Heart Challenge',
                'description' => 'No extra hearts. Stay at 3 hearts.',
                'games' => ['The Legend of Zelda: Twilight Princess (2006)'],
                'rules' => [
                    ['name' => '3 Hearts Only', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Zelda TP: No Wolf Form',
                'description' => 'Human form only when optional. No wolf abilities.',
                'games' => ['The Legend of Zelda: Twilight Princess (2006)'],
                'rules' => [
                    ['name' => 'No Wolf Form', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Zelda TP: No Bottle/Fairy',
                'description' => 'No healing items. Pure skill required.',
                'games' => ['The Legend of Zelda: Twilight Princess (2006)'],
                'rules' => [
                    ['name' => 'No Bottles', 'is_default' => true],
                ],
            ],

            // === ZELDA: THE WIND WAKER (3) ===
            [
                'name' => 'Zelda TWW: 3 Heart Challenge',
                'description' => 'No extra hearts. 3 hearts maximum.',
                'games' => ['The Legend of Zelda: The Wind Waker (2002)'],
                'rules' => [
                    ['name' => '3 Hearts Only', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Zelda TWW: No Tingle Tuner',
                'description' => 'No helper items or cheats. Self-reliant gameplay.',
                'games' => ['The Legend of Zelda: The Wind Waker (2002)'],
                'rules' => [
                    ['name' => 'No Helper Items', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Zelda TWW: No Bottle/Fairy',
                'description' => 'No healing items. Navigate the seas without safety.',
                'games' => ['The Legend of Zelda: The Wind Waker (2002)'],
                'rules' => [
                    ['name' => 'No Bottles', 'is_default' => true],
                ],
            ],

            // === ZELDA: SKYWARD SWORD (3) ===
            [
                'name' => 'Zelda SS: 3 Heart Challenge',
                'description' => 'No extra hearts. Maximum difficulty.',
                'games' => ['The Legend of Zelda: Skyward Sword (2011)'],
                'rules' => [
                    ['name' => '3 Hearts Only', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Zelda SS: No Shield',
                'description' => 'Cannot use shields. Dodge or take damage.',
                'games' => ['The Legend of Zelda: Skyward Sword (2011)'],
                'rules' => [
                    ['name' => 'No Shield', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Zelda SS: No Bottle/Potion',
                'description' => 'No healing items or potions. Pure combat skill.',
                'games' => ['The Legend of Zelda: Skyward Sword (2011)'],
                'rules' => [
                    ['name' => 'No Bottles', 'is_default' => true],
                ],
            ],

            // === ZELDA: A LINK TO THE PAST (3) ===
            [
                'name' => 'Zelda ALTTP: 3 Heart Challenge',
                'description' => 'No extra hearts. Classic difficulty.',
                'games' => ['The Legend of Zelda: A Link to the Past (1991)'],
                'rules' => [
                    ['name' => '3 Hearts Only', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Zelda ALTTP: No Bottle',
                'description' => 'Cannot use bottles. No storing items.',
                'games' => ['The Legend of Zelda: A Link to the Past (1991)'],
                'rules' => [
                    ['name' => 'No Bottles', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Zelda ALTTP: No Armor Upgrades',
                'description' => 'Green tunic only. No defense buffs.',
                'games' => ['The Legend of Zelda: A Link to the Past (1991)'],
                'rules' => [
                    ['name' => 'No Armor Upgrades', 'is_default' => true],
                ],
            ],

            // === ZELDA: LINK'S AWAKENING (3) ===
            [
                'name' => 'Zelda LA: 3 Heart Challenge',
                'description' => 'No extra hearts. Hardcore mode.',
                'games' => ['The Legend of Zelda: Link\'s Awakening (1993)'],
                'rules' => [
                    ['name' => '3 Hearts Only', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Zelda LA: No Guardian Acorns/Pieces',
                'description' => 'No defense upgrades. Base stats only.',
                'games' => ['The Legend of Zelda: Link\'s Awakening (1993)'],
                'rules' => [
                    ['name' => 'No Defense Upgrades', 'is_default' => true],
                ],
            ],
            [
                'name' => 'Zelda LA: Swordless',
                'description' => 'Alternative weapons only. Creative combat required.',
                'games' => ['The Legend of Zelda: Link\'s Awakening (1993)'],
                'rules' => [
                    ['name' => 'No Sword', 'is_default' => true],
                ],
            ],

            // === THE WITNESS (3) ===
            [
                'name' => 'The Witness: No Guide Challenge',
                'description' => 'Cannot look up puzzle solutions. Pure logic solving.',
                'games' => ['The Witness (2016)'],
                'rules' => [
                    ['name' => 'No Guides', 'is_default' => true],
                ],
            ],
            [
                'name' => 'The Witness: Speed Challenge',
                'description' => 'Complete within time limit. Speedrun pressure.',
                'games' => ['The Witness (2016)'],
                'rules' => [
                    ['name' => 'Time Limit', 'is_default' => true],
                ],
            ],
            [
                'name' => 'The Witness: Random Start',
                'description' => 'Must complete areas in non-optimal order. Navigation challenge.',
                'games' => ['The Witness (2016)'],
                'rules' => [
                    ['name' => 'Random Area Order', 'is_default' => true],
                ],
            ],

            // === RESIDENT EVIL (1996) (3) ===
            [
                'name' => 'RE1: Knife Only Challenge',
                'description' => 'Can only use knife. No guns. Classic survival horror test.',
                'games' => ['Resident Evil (1996)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE1: No Item Box Challenge',
                'description' => 'Cannot use item boxes. Pure inventory management challenge.',
                'games' => ['Resident Evil (1996)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ITEMBOX, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE1: No Healing Challenge',
                'description' => 'Cannot use healing items. One mistake away from death.',
                'games' => ['Resident Evil (1996)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'is_default' => true],
                ],
            ],

            // === RESIDENT EVIL 2 (1998) (3) ===
            [
                'name' => 'RE2 1998: Knife Only Challenge',
                'description' => 'Can only use knife. Pure skill required.',
                'games' => ['Resident Evil 2 (1998)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE2 1998: No Item Box Challenge',
                'description' => 'Cannot use item boxes. Inventory management extreme.',
                'games' => ['Resident Evil 2 (1998)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ITEMBOX, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE2 1998: Handgun Only Challenge',
                'description' => 'Can only use handguns. No heavy weapons.',
                'games' => ['Resident Evil 2 (1998)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_HANDGUN_ONLY, 'is_default' => true],
                ],
            ],

            // === RESIDENT EVIL 2 (2019) (3) ===
            [
                'name' => 'RE2 2019: Knife Only Challenge',
                'description' => 'Can only use knife. Legendary difficulty.',
                'games' => ['Resident Evil 2 (2019)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE2 2019: S+ Rank Run',
                'description' => 'No item box and limited saves. Speedrun challenge.',
                'games' => ['Resident Evil 2 (2019)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ITEMBOX, 'is_default' => true],
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SAVE, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE2 2019: Handgun Only Challenge',
                'description' => 'Can only use handguns. Ammo conservation critical.',
                'games' => ['Resident Evil 2 (2019)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_HANDGUN_ONLY, 'is_default' => true],
                ],
            ],

            // === RESIDENT EVIL 3: NEMESIS (1999) (3) ===
            [
                'name' => 'RE3 1999: Knife Only Challenge',
                'description' => 'Can only use knife. Face Nemesis with blade only.',
                'games' => ['Resident Evil 3: Nemesis (1999)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE3 1999: No Item Box Challenge',
                'description' => 'Cannot use item boxes. Escape with limited space.',
                'games' => ['Resident Evil 3: Nemesis (1999)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ITEMBOX, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE3 1999: No Dodge Challenge',
                'description' => 'Cannot use dodge mechanic. No quick steps.',
                'games' => ['Resident Evil 3: Nemesis (1999)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_DODGE, 'is_default' => true],
                ],
            ],

            // === RESIDENT EVIL 3 (2020) (3) ===
            [
                'name' => 'RE3 2020: Knife Only Challenge',
                'description' => 'Can only use knife. Modern Nemesis vs blade.',
                'games' => ['Resident Evil 3 (2020)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE3 2020: No Item Box Challenge',
                'description' => 'Cannot use item boxes. Pure survival.',
                'games' => ['Resident Evil 3 (2020)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ITEMBOX, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE3 2020: No Dodge Challenge',
                'description' => 'Cannot use dodge mechanic. Face threats head-on.',
                'games' => ['Resident Evil 3 (2020)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_DODGE, 'is_default' => true],
                ],
            ],

            // === RESIDENT EVIL 4 (2005) (3) ===
            [
                'name' => 'RE4 2005: Knife Only Challenge',
                'description' => 'Can only use knife. Legendary challenge.',
                'games' => ['Resident Evil 4 (2005)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE4 2005: No Merchant Challenge',
                'description' => 'Cannot use merchant. No upgrades, no purchases.',
                'games' => ['Resident Evil 4 (2005)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SHOP, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE4 2005: Handgun Only Challenge',
                'description' => 'Can only use handguns. Classic restriction.',
                'games' => ['Resident Evil 4 (2005)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_HANDGUN_ONLY, 'is_default' => true],
                ],
            ],

            // === RESIDENT EVIL 4 (2023) (3) ===
            [
                'name' => 'RE4 2023: Knife Only Challenge',
                'description' => 'Can only use knife. Remake difficulty.',
                'games' => ['Resident Evil 4 (2023)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE4 2023: No Merchant Challenge',
                'description' => 'Cannot use merchant. Base equipment only.',
                'games' => ['Resident Evil 4 (2023)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SHOP, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE4 2023: Handgun Only Challenge',
                'description' => 'Can only use handguns. Precision required.',
                'games' => ['Resident Evil 4 (2023)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_HANDGUN_ONLY, 'is_default' => true],
                ],
            ],

            // === RESIDENT EVIL 5 (2009) (3) ===
            [
                'name' => 'RE5: Knife Only Challenge',
                'description' => 'Can only use knife. Co-op or solo blade mastery.',
                'games' => ['Resident Evil 5 (2009)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE5: No Item Box Challenge',
                'description' => 'Cannot use item boxes. Limited inventory run.',
                'games' => ['Resident Evil 5 (2009)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ITEMBOX, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE5: Handgun Only Challenge',
                'description' => 'Can only use handguns. Ammo management critical.',
                'games' => ['Resident Evil 5 (2009)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_HANDGUN_ONLY, 'is_default' => true],
                ],
            ],

            // === RESIDENT EVIL 6 (2012) (3) ===
            [
                'name' => 'RE6: Knife Only Challenge',
                'description' => 'Can only use knife. Action horror blade run.',
                'games' => ['Resident Evil 6 (2012)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE6: No Healing Challenge',
                'description' => 'Cannot use healing items. Survival difficulty.',
                'games' => ['Resident Evil 6 (2012)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE6: Handgun Only Challenge',
                'description' => 'Can only use handguns. Slow the action down.',
                'games' => ['Resident Evil 6 (2012)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_HANDGUN_ONLY, 'is_default' => true],
                ],
            ],

            // === RESIDENT EVIL 7 (3) ===
            [
                'name' => 'RE7: Knife Only Challenge',
                'description' => 'Can only use knife. First-person horror melee.',
                'games' => ['Resident Evil 7'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE7: No Item Box Challenge',
                'description' => 'Cannot use item boxes. Baker house pure survival.',
                'games' => ['Resident Evil 7'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ITEMBOX, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE7: No Healing Challenge',
                'description' => 'Cannot use healing items. Face the Bakers at low health.',
                'games' => ['Resident Evil 7'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'is_default' => true],
                ],
            ],

            // === RESIDENT EVIL 7: BIOHAZARD (2017) (3) ===
            [
                'name' => 'RE7 Biohazard: Knife Only Challenge',
                'description' => 'Can only use knife. Ethan vs Bakers with blade.',
                'games' => ['Resident Evil 7: Biohazard (2017)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE7 Biohazard: No Item Box Challenge',
                'description' => 'Cannot use item boxes. Hardcore inventory management.',
                'games' => ['Resident Evil 7: Biohazard (2017)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ITEMBOX, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE7 Biohazard: No Healing Challenge',
                'description' => 'Cannot use healing items. Maximum tension.',
                'games' => ['Resident Evil 7: Biohazard (2017)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'is_default' => true],
                ],
            ],

            // === RESIDENT EVIL CODE: VERONICA (2000) (3) ===
            [
                'name' => 'RE Code Veronica: Knife Only Challenge',
                'description' => 'Can only use knife. Classic survival challenge.',
                'games' => ['Resident Evil Code: Veronica (2000)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE Code Veronica: No Item Box Challenge',
                'description' => 'Cannot use item boxes. Rockfort Island hardcore.',
                'games' => ['Resident Evil Code: Veronica (2000)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_ITEMBOX, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE Code Veronica: No Healing Challenge',
                'description' => 'Cannot use healing items. Pure skill required.',
                'games' => ['Resident Evil Code: Veronica (2000)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'is_default' => true],
                ],
            ],

            // === RESIDENT EVIL VILLAGE (2021) (3) ===
            [
                'name' => 'RE Village: Knife Only Challenge',
                'description' => 'Can only use knife. Face the Lords with blade only.',
                'games' => ['Resident Evil Village (2021)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_KNIFE_ONLY, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE Village: No Merchant Challenge',
                'description' => 'Cannot use Duke\'s shop. No upgrades or purchases.',
                'games' => ['Resident Evil Village (2021)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_SHOP, 'is_default' => true],
                ],
            ],
            [
                'name' => 'RE Village: No Healing Challenge',
                'description' => 'Cannot use healing items. Village of shadows difficulty.',
                'games' => ['Resident Evil Village (2021)'],
                'rules' => [
                    ['reference' => PermanentLegendaryRulesFixtures::RULE_NO_HEAL, 'is_default' => true],
                ],
            ],
        ];
    }
}
