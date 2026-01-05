<?php

namespace App\Command;

use App\Repository\RuleIconRepository;
use App\Repository\RuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:auto-assign-icons-to-rules',
    description: 'Automatically assign icons to rules based on name matching'
)]
class AutoAssignIconsToRulesCommand extends Command
{
    // Manual mappings for rules that don't match automatically
    // Order matters - more specific matches should come first
    private const MANUAL_MAPPINGS = [
        // Weapons - Specific first
        'sniper only' => 'sniper-rifle',
        'sniper rifle' => 'sniper-rifle',
        'machine gun only' => 'mp5',
        'machine gun' => 'mp5',
        'handgun only' => 'pistol-gun',
        'handgun' => 'pistol-gun',
        'pistol only' => 'pistol-gun',
        'pistol' => 'pistol-gun',
        'rifle' => 'musket',
        'shotgun' => 'pump-shotgun',
        'knife only' => 'stiletto',
        'knife' => 'stiletto',
        'melee only' => 'crossed-swords',
        'melee' => 'crossed-swords',
        'melee attack' => 'punch',
        'sword' => 'sword-brandish',
        'katana' => 'katana',
        'axe' => 'wood-axe',
        'mace' => 'mace',
        'spear' => 'spear-hook',
        'bow' => 'arrow-cluster',
        'crossbow' => 'crossbow',
        'grenade' => 'grenade',
        'throw grenade' => 'grenade',
        'rocket' => 'rocket-launcher',
        'mine' => 'land-mine',
        'primary weapon only' => 'pistol-gun',
        'sidearm' => 'pistol-gun',
        
        // Movement - Specific first
        'walking only' => 'walking-boot',
        'only walking' => 'walking-boot',
        'no running' => 'walking-boot',
        'walk' => 'walking-boot',
        'sprint only' => 'sprint',
        'sprint' => 'sprint',
        'no double jump' => 'jump',
        'no jumping' => 'jump',
        'jump' => 'jump',
        'crouch only' => 'crouching',
        'crouch' => 'crouching',
        'no dodging' => 'dodge',
        'no dodge' => 'dodge',
        'dodge' => 'dodge',
        'dodging' => 'dodge',
        'pacifist' => 'dodge',
        'pacifist mode' => 'dodge',
        'pacifist run' => 'dodge',
        'climb' => 'mountain-climbing',
        'dash' => 'sprint-foot',
        'no dash' => 'sprint-foot',
        'ground only' => 'walking-boot',
        
        // Resources - Specific first
        'no healing' => 'heart-broken',
        'no heal' => 'heart-broken',
        'use healing item' => 'health-normal',
        'healing item' => 'health-normal',
        'healing' => 'health-normal',
        'health' => 'health-normal',
        'no ammo pickup' => 'ammo-box',
        'no ammo pickups' => 'ammo-box',
        'ammo' => 'ammo-box',
        'ammunition' => 'ammo-box',
        'no shield pickup' => 'round-shield',
        'no shield' => 'round-shield',
        'shield' => 'round-shield',
        'no armor' => 'armor-vest',
        'armor' => 'armor-vest',
        'potion' => 'potion-ball',
        'medkit' => 'med-pack-alt',
        'med kit' => 'med-pack-alt',
        'syringe' => 'syringe',
        'key' => 'key',
        'gold' => 'gold-bar',
        'coin' => 'two-coins',
        'no item box' => 'locked-chest',
        'no itembox' => 'locked-chest',
        'no item pickups' => 'locked-chest',
        'no item pickup' => 'locked-chest',
        'no consumables' => 'potion-ball',
        'starting equipment only' => 'knapsack',
        'starter equipment only' => 'knapsack',
        'no equipment' => 'locked-chest',
        'no accessories' => 'locked-chest',
        
        // Actions - Specific first
        'no reloading' => 'reload',
        'no reload' => 'reload',
        'reload weapon' => 'reload',
        'reload' => 'reload',
        'ads only' => 'crosshair',
        'no ads' => 'crosshair',
        'aim down sights' => 'crosshair',
        'aim' => 'targeting',
        'no blocking' => 'fire-shield',
        'block' => 'fire-shield',
        'die to enemy' => 'skull',
        'kill yourself' => 'skull',
        'death' => 'skull',
        'no damage allowed' => 'bleeding-heart',
        'get hit by enemy' => 'bleeding-heart',
        'take fall damage' => 'bleeding-heart',
        'damage' => 'bleeding-heart',
        'backstab' => 'backstab',
        'punch' => 'punch',
        'light attacks only' => 'punch',
        'light attack' => 'punch',
        
        // Modifiers - Specific first
        'no save' => 'save',
        'no saving' => 'save',
        'use save point' => 'save',
        'save' => 'save',
        'reload a save' => 'load',
        'load' => 'load',
        'no map' => 'treasure-map',
        'map' => 'treasure-map',
        'no fast travel' => 'fast-forward-button',
        'fast travel' => 'fast-forward-button',
        'no recall' => 'fast-forward-button',
        'no shop' => 'shopping-cart',
        'no shop/merchant' => 'shopping-cart',
        'no merchant' => 'shopping-cart',
        'purchase item' => 'shopping-cart',
        'shop' => 'shopping-cart',
        'talk to npc' => 'conversation',
        'dialogue' => 'conversation',
        'free pass' => 'jester-hat',
        'wildcard' => 'clown',
        'random character' => 'clown',
        'prohibited' => 'locked-chest',
        'no' => 'locked-chest',
        'ban' => 'locked-chest',
        'timer' => 'timer',
        'time' => 'timer',
        'counter' => 'counter',
        'count' => 'counter',
        'no hud' => 'prohibited',
        'no hiding' => 'prohibited',
        'no safe rooms' => 'prohibited',
        'enter safe room' => 'prohibited',
        'no objectives' => 'prohibited',
        'complete objective' => 'prohibited',
        'no killstreak' => 'prohibited',
        'no killstreaks' => 'prohibited',
        'no warding' => 'prohibited',
        'no wards' => 'prohibited',
        'no ganking' => 'prohibited',
        'no rerolls' => 'rolling-dice-cup',
        'no reroll' => 'rolling-dice-cup',
        
        // Magic/Abilities - Specific first
        'only magic' => 'crystal-wand',
        'no magic' => 'magic-swirl',
        'magic' => 'magic-swirl',
        'spell' => 'crystal-wand',
        'fireball' => 'fireball',
        'no special moves' => 'fireball',
        'special move' => 'fireball',
        'use special ability' => 'fireball',
        'no super moves' => 'round-star',
        'super move' => 'round-star',
        'no ultimate' => 'round-star',
        'no ultimate ability' => 'round-star',
        'ultimate' => 'round-star',
        'ice' => 'ice-bolt',
        'lightning' => 'lightning-bolt',
        
        // Ranged/Melee
        'ranged only' => 'arrow-cluster',
        'no ranged' => 'crossbow',
        
        // Horror - Specific first
        'no flashlight' => 'flashlight',
        'flashlight' => 'flashlight',
        'horror' => 'bleeding-eye',
        'bat' => 'bat',
        'chainsaw' => 'chainsaw',
        
        // RPG
        'spellbook' => 'spell-book',
        'scroll' => 'scroll-unfurled',
        'inventory' => 'knapsack',
        'treasure' => 'treasure-chest',
        'open container' => 'treasure-chest',
        'find secret' => 'treasure-chest',
        'first item only' => 'treasure-chest',
        'cursed items only' => 'skull',
        'achievement' => 'laurels',
        'no crafting' => 'campfire',
        'no crafting items' => 'campfire',
        'crafting' => 'campfire',
        'no building' => 'stone-tower',
        'no building construction' => 'stone-tower',
        'no building shelter' => 'castle',
        'no base' => 'castle',
        'no defensive structures' => 'castle',
        'no unit production' => 'barracks',
        'starting units only' => 'chess-pawn',
        'starting lane only' => 'prohibited',
        'no tech upgrades' => 'upgrade',
        'no upgrades' => 'upgrade',
        'no leveling up' => 'upgrade',
        'no leveling' => 'upgrade',
        'no quest markers' => 'treasure-map',
        'no last hitting' => 'crossed-swords',
        'no jungle camps' => 'bat',
        'no warding' => 'prohibited',
        'no wards' => 'prohibited',
        
        // Souls-like
        'no parrying' => 'fire-shield',
        'no parry' => 'fire-shield',
        'no summons' => 'prohibited',
        'fat roll only' => 'dodge',
        
        // Fighting Games
        'no grabs' => 'punch',
        'no grab' => 'punch',
        
        // Metroidvania
        'no backtracking' => 'prohibited',
        'sequence break forbidden' => 'prohibited',
        'no double jump' => 'jump',
        
        // Survival
        'no eating food' => 'meat',
        'no drinking water' => 'water-drop',
        'no resource gathering' => 'mining',
        
        // Counter Rules
        'fire sniper rifle' => 'sniper-rifle',
        'fire machine gun' => 'mp5',
        'fire pistol' => 'pistol-gun',
        'fire shotgun' => 'pump-shotgun',
        'pick up item' => 'treasure-chest',
        'drop item' => 'treasure-chest',
        'open door' => 'key',
        'boss encounter' => 'skull',
        'no room clearing' => 'sprint',
    ];

    public function __construct(
        private readonly RuleRepository $ruleRepository,
        private readonly RuleIconRepository $ruleIconRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be matched without actually updating')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Overwrite existing icon assignments')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');
        $force = $input->getOption('force');

        $io->title('Auto-Assign Icons to Rules');

        // Get all rules and icons
        $rules = $this->ruleRepository->findAll();
        $icons = $this->ruleIconRepository->findAll();

        if (empty($rules)) {
            $io->warning('No rules found in database.');
            return Command::SUCCESS;
        }

        if (empty($icons)) {
            $io->warning('No icons found in database. Run "make download-icons" first.');
            return Command::SUCCESS;
        }

        // Create icon lookup maps
        $iconsByIdentifier = [];
        $iconsByDisplayName = [];
        foreach ($icons as $icon) {
            $iconsByIdentifier[strtolower($icon->getIdentifier())] = $icon;
            $iconsByDisplayName[strtolower($icon->getDisplayName())] = $icon;
        }

        $matched = 0;
        $skipped = 0;
        $updated = 0;
        $matches = [];

        $io->progressStart(count($rules));

        foreach ($rules as $rule) {
            // Skip if already has icon and not forcing
            if (!$force && $rule->getIconIdentifier() !== null) {
                ++$skipped;
                $io->progressAdvance();
                continue;
            }

            $ruleName = strtolower($rule->getName() ?? '');
            $matchedIcon = $this->findBestMatch($ruleName, $iconsByIdentifier, $iconsByDisplayName, $icons);

            if ($matchedIcon) {
                $wasEmpty = $rule->getIconIdentifier() === null;
                $matches[] = [
                    'rule' => $rule->getName(),
                    'icon' => $matchedIcon->getIdentifier(),
                    'iconName' => $matchedIcon->getDisplayName(),
                    'wasEmpty' => $wasEmpty,
                ];

                if (!$dryRun) {
                    $rule->setIconIdentifier($matchedIcon->getIdentifier());
                    if ($wasEmpty) {
                        ++$matched;
                    } else {
                        ++$updated;
                    }
                } else {
                    ++$matched;
                }
            }

            $io->progressAdvance();
        }

        $io->progressFinish();

        if (!$dryRun) {
            $this->entityManager->flush();
        }

        $io->newLine();
        $io->success([
            sprintf('Matched: %d rules', $matched),
            sprintf('Updated: %d existing assignments', $updated),
            sprintf('Skipped: %d rules (already have icons)', $skipped),
            sprintf('Total processed: %d rules', count($rules)),
        ]);

        if (!empty($matches)) {
            $io->section('Matched Rules');
            $tableRows = [];
            foreach ($matches as $match) {
                $tableRows[] = [
                    $match['rule'],
                    $match['iconName'] . ' (' . $match['icon'] . ')',
                    $match['wasEmpty'] ? 'New' : 'Updated',
                ];
            }
            $io->table(['Rule', 'Icon', 'Status'], $tableRows);
        }

        if ($dryRun) {
            $io->note('This was a dry run. Run without --dry-run to apply changes.');
        }

        return Command::SUCCESS;
    }

    private function findBestMatch(string $ruleName, array $iconsByIdentifier, array $iconsByDisplayName, array $allIcons): ?\App\Entity\RuleIcon
    {
        // Normalize rule name for matching
        $normalizedRuleName = $this->normalizeName($ruleName);

        // 1. Check manual mappings first
        foreach (self::MANUAL_MAPPINGS as $key => $iconIdentifier) {
            if (strpos($normalizedRuleName, $key) !== false) {
                $icon = $iconsByIdentifier[strtolower($iconIdentifier)] ?? null;
                if ($icon) {
                    return $icon;
                }
            }
        }

        // 2. Check for exact identifier match
        foreach ($iconsByIdentifier as $identifier => $icon) {
            if (strpos($normalizedRuleName, $identifier) !== false) {
                return $icon;
            }
        }

        // 3. Check for display name match
        foreach ($iconsByDisplayName as $displayName => $icon) {
            $normalizedDisplayName = $this->normalizeName($displayName);
            if (strpos($normalizedRuleName, $normalizedDisplayName) !== false || 
                strpos($normalizedDisplayName, $normalizedRuleName) !== false) {
                return $icon;
            }
        }

        // 4. Check icon tags
        foreach ($allIcons as $icon) {
            $tags = $icon->getTags() ?? [];
            foreach ($tags as $tag) {
                $normalizedTag = $this->normalizeName($tag);
                if (strpos($normalizedRuleName, $normalizedTag) !== false) {
                    return $icon;
                }
            }
        }

        // 5. Fuzzy matching - check if rule name contains any part of icon identifier/display name
        $ruleWords = explode(' ', $normalizedRuleName);
        foreach ($ruleWords as $word) {
            if (strlen($word) < 3) {
                continue; // Skip very short words
            }

            // Check identifier
            foreach ($iconsByIdentifier as $identifier => $icon) {
                if (strpos($identifier, $word) !== false || strpos($word, $identifier) !== false) {
                    return $icon;
                }
            }

            // Check display name
            foreach ($iconsByDisplayName as $displayName => $icon) {
                $normalizedDisplayName = $this->normalizeName($displayName);
                if (strpos($normalizedDisplayName, $word) !== false || strpos($word, $normalizedDisplayName) !== false) {
                    return $icon;
                }
            }
        }

        return null;
    }

    private function normalizeName(string $name): string
    {
        // Convert to lowercase, remove special characters, normalize spaces
        $normalized = strtolower($name);
        $normalized = preg_replace('/[^a-z0-9\s]/', '', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        return trim($normalized);
    }
}

