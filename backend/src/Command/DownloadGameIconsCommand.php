<?php

namespace App\Command;

use App\Entity\RuleIcon;
use App\Repository\RuleIconRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:download-game-icons',
    description: 'Download curated SVG icons from game-icons.net GitHub repository for rules'
)]
class DownloadGameIconsCommand extends Command
{
    // Curated list of gaming-relevant icons from game-icons.net GitHub repo
    // Format: [filename, category, display_name, [tags]]
    private const ICON_LIST = [
        // Weapons - Melee
        ['sword-brandish', 'weapon', 'Sword', ['melee', 'blade', 'weapon']],
        ['katana', 'weapon', 'Katana', ['melee', 'blade', 'weapon']],
        ['wood-axe', 'weapon', 'Axe', ['melee', 'weapon']],
        ['mace', 'weapon', 'Mace', ['melee', 'weapon']],
        ['war-pick', 'weapon', 'War Pick', ['melee', 'weapon']],
        ['wood-club', 'weapon', 'Club', ['melee', 'weapon']],
        ['stiletto', 'weapon', 'Dagger', ['melee', 'blade', 'weapon']],
        ['spear-hook', 'weapon', 'Spear', ['melee', 'weapon']],

        // Weapons - Ranged
        ['arrow-cluster', 'weapon', 'Bow', ['ranged', 'weapon']],
        ['crossbow', 'weapon', 'Crossbow', ['ranged', 'weapon']],
        ['thrown-daggers', 'weapon', 'Throwing Daggers', ['ranged', 'weapon']],

        // Weapons - Firearms
        ['pistol-gun', 'weapon', 'Pistol', ['firearm', 'ranged', 'weapon']],
        ['tommy-gun', 'weapon', 'SMG', ['firearm', 'ranged', 'weapon']],
        ['mp5', 'weapon', 'MP5', ['firearm', 'ranged', 'weapon']],
        ['winchester-rifle', 'weapon', 'Rifle', ['firearm', 'ranged', 'weapon']],
        ['sniper-rifle', 'weapon', 'Sniper Rifle', ['firearm', 'ranged', 'weapon']],
        ['pump-shotgun', 'weapon', 'Shotgun', ['firearm', 'ranged', 'weapon']],
        ['kalashnikov', 'weapon', 'Assault Rifle', ['firearm', 'ranged', 'weapon']],

        // Weapons - Explosive
        ['grenade', 'weapon', 'Grenade', ['explosive', 'weapon']],
        ['rocket-launcher', 'weapon', 'Rocket Launcher', ['explosive', 'weapon']],
        ['land-mine', 'weapon', 'Mine', ['explosive', 'weapon']],

        // Magic & Abilities
        ['magic-swirl', 'ability', 'Magic', ['magic', 'ability']],
        ['crystal-wand', 'ability', 'Magic Wand', ['magic', 'ability']],
        ['fireball', 'ability', 'Fireball', ['magic', 'spell', 'ability']],
        ['ice-bolt', 'ability', 'Ice Spell', ['magic', 'spell', 'ability']],
        ['lightning-bolt', 'ability', 'Lightning', ['magic', 'spell', 'ability']],
        ['round-star', 'ability', 'Ultimate Ability', ['ability', 'ultimate']],

        // Movement & Actions
        ['sprint', 'movement', 'Sprint', ['movement', 'action']],
        ['walking-boot', 'movement', 'Walk', ['movement', 'action']],
        ['jump-across', 'movement', 'Jump', ['movement', 'action']],
        ['dodge', 'movement', 'Dodge', ['movement', 'action', 'defense']],
        ['rolling-dice-cup', 'action', 'Roll', ['movement', 'action']],
        ['crouching', 'movement', 'Crouch', ['movement', 'action']],
        ['jump', 'movement', 'Double Jump', ['movement', 'action']],
        ['sprint-foot', 'movement', 'Dash', ['movement', 'action']],
        ['mountain-climbing', 'movement', 'Climb', ['movement', 'action']],

        // Resources & Items
        ['health-normal', 'resource', 'Health', ['resource', 'health']],
        ['heart-bottle', 'resource', 'Health Potion', ['resource', 'health', 'item']],
        ['med-pack-alt', 'resource', 'Med Kit', ['resource', 'health', 'item']],
        ['syringe', 'resource', 'Syringe', ['resource', 'health', 'item']],
        ['potion-ball', 'resource', 'Potion', ['resource', 'item']],
        ['ammo-box', 'resource', 'Ammo', ['resource', 'ammo']],
        ['round-shield', 'resource', 'Shield', ['resource', 'defense']],
        ['armor-vest', 'resource', 'Armor', ['resource', 'defense']],
        ['key', 'resource', 'Key', ['resource', 'item']],
        ['gold-bar', 'resource', 'Gold', ['resource', 'currency']],
        ['two-coins', 'resource', 'Coin', ['resource', 'currency']],

        // Combat & Status
        ['crossed-swords', 'combat', 'Combat', ['combat']],
        ['punch', 'combat', 'Melee Attack', ['combat', 'melee']],
        ['targeting', 'combat', 'Aim', ['combat', 'aiming']],
        ['crosshair', 'combat', 'ADS', ['combat', 'aiming']],
        ['fire-shield', 'combat', 'Block', ['combat', 'defense']],
        ['skull', 'combat', 'Death', ['combat', 'death']],
        ['bleeding-heart', 'combat', 'Damage', ['combat', 'damage']],
        ['backstab', 'combat', 'Backstab', ['combat', 'stealth']],

        // Strategy & MOBA
        ['stone-tower', 'strategy', 'Tower', ['strategy', 'building']],
        ['castle', 'strategy', 'Castle', ['strategy', 'building']],
        ['barracks', 'strategy', 'Barracks', ['strategy', 'building']],
        ['mining', 'strategy', 'Resource Gather', ['strategy', 'resource']],
        ['upgrade', 'strategy', 'Upgrade', ['strategy', 'progression']],
        ['aged', 'strategy', 'Tech Tree', ['strategy', 'progression']],
        ['chess-pawn', 'strategy', 'Unit', ['strategy', 'unit']],

        // Survival & Crafting
        ['campfire', 'survival', 'Campfire', ['survival', 'crafting']],
        ['wood-pile', 'survival', 'Wood', ['survival', 'resource']],
        ['stone-pile', 'survival', 'Stone', ['survival', 'resource']],
        ['meat', 'survival', 'Food', ['survival', 'resource']],
        ['water-drop', 'survival', 'Water', ['survival', 'resource']],
        ['thermometer', 'survival', 'Temperature', ['survival', 'status']],

        // UI & Modifiers
        ['skills', 'modifier', 'Skill Tree', ['modifier', 'progression']],
        ['locked-chest', 'modifier', 'Locked', ['modifier', 'restriction']],
        ['save', 'modifier', 'Save', ['modifier', 'action']],
        ['load', 'modifier', 'Load', ['modifier', 'action']],
        ['treasure-map', 'modifier', 'Map', ['modifier', 'navigation']],
        ['compass', 'modifier', 'Compass', ['modifier', 'navigation']],
        ['fast-forward-button', 'modifier', 'Fast Travel', ['modifier', 'movement']],
        ['conversation', 'modifier', 'Dialogue', ['modifier', 'action']],
        ['shopping-cart', 'modifier', 'Shop', ['modifier', 'action']],

        // Horror & Atmosphere
        ['bleeding-eye', 'horror', 'Horror', ['horror', 'atmosphere']],
        ['bat', 'horror', 'Bat', ['horror', 'enemy']],
        ['chainsaw', 'horror', 'Chainsaw', ['horror', 'weapon']],
        ['flashlight', 'horror', 'Flashlight', ['horror', 'item']],

        // RPG Specific
        ['spell-book', 'rpg', 'Spellbook', ['rpg', 'magic']],
        ['scroll-unfurled', 'rpg', 'Scroll', ['rpg', 'magic']],
        ['knapsack', 'rpg', 'Inventory', ['rpg', 'item']],
        ['treasure-chest', 'rpg', 'Treasure', ['rpg', 'item']],
        ['laurels', 'rpg', 'Achievement', ['rpg', 'progression']],

        // Free Pass / Joker
        ['jester-hat', 'modifier', 'Free Pass', ['modifier', 'joker']],
        ['clown', 'modifier', 'Wildcard', ['modifier', 'joker']],
    ];

    private const REPO_URL = 'https://github.com/game-icons/icons.git';
    private const TEMP_DIR = '/tmp/game-icons-download';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RuleIconRepository $ruleIconRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('skip-existing', null, InputOption::VALUE_NONE, 'Skip icons that already exist in database')
            ->addOption('update-existing', null, InputOption::VALUE_NONE, 'Update existing icons with new SVG data')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $skipExisting = $input->getOption('skip-existing');
        $updateExisting = $input->getOption('update-existing');

        $io->title('Downloading Gaming Icons from game-icons.net GitHub');
        $io->text([
            'This will download ' . count(self::ICON_LIST) . ' curated gaming icons',
            'Source: https://github.com/game-icons/icons',
            'License: CC BY 3.0',
            'Attribution: Various artists from game-icons.net',
        ]);

        // Step 1: Clone/update the repository
        $io->section('Step 1: Fetching game-icons repository');
        if (!$this->prepareRepository($io)) {
            $io->error('Failed to fetch repository. Make sure git is installed.');

            return Command::FAILURE;
        }

        // Step 2: Import icons
        $io->section('Step 2: Importing icons into database');
        $io->progressStart(count(self::ICON_LIST));

        $downloaded = 0;
        $skipped = 0;
        $updated = 0;
        $failed = [];

        foreach (self::ICON_LIST as $iconData) {
            [$filename, $category, $displayName, $tags] = $iconData;
            $identifier = $filename;

            $existingIcon = $this->ruleIconRepository->findOneBy(['identifier' => $identifier]);

            if ($existingIcon && $skipExisting && !$updateExisting) {
                ++$skipped;
                $io->progressAdvance();
                continue;
            }

            try {
                // Find the icon file in the repository
                $svgPath = $this->findIconInRepo($filename);

                if (!$svgPath) {
                    $failed[] = sprintf('%s: File not found in repository', $filename);
                    $io->progressAdvance();
                    continue;
                }

                $svgContent = file_get_contents($svgPath);
                $artist = $this->getArtistFromPath($svgPath);

                if ($existingIcon && $updateExisting) {
                    // Update existing icon
                    $existingIcon->setSvgContent($svgContent);
                    $existingIcon->setDisplayName($displayName);
                    $existingIcon->setCategory($category);
                    $existingIcon->setTags($tags);
                    ++$updated;
                } else {
                    // Create new icon
                    $icon = new RuleIcon();
                    $icon->setIdentifier($identifier);
                    $icon->setDisplayName($displayName);
                    $icon->setCategory($category);
                    $icon->setSvgContent($svgContent);
                    $icon->setTags($tags);
                    $icon->setColor('#FFFFFF');
                    $icon->setLicense('CC BY 3.0');
                    $icon->setSource(sprintf('game-icons.net (artist: %s)', $artist));

                    $this->entityManager->persist($icon);
                    ++$downloaded;
                }

                // Batch flush every 20 icons
                if (($downloaded + $updated) % 20 === 0) {
                    $this->entityManager->flush();
                }
            } catch (\Exception $e) {
                $failed[] = sprintf('%s: %s', $filename, $e->getMessage());
            }

            $io->progressAdvance();
        }

        // Final flush
        $this->entityManager->flush();
        $io->progressFinish();

        $io->newLine();
        $io->success([
            sprintf('Downloaded: %d new icons', $downloaded),
            sprintf('Updated: %d existing icons', $updated),
            sprintf('Skipped: %d icons', $skipped),
            sprintf('Failed: %d icons', count($failed)),
        ]);

        if (count($failed) > 0) {
            $io->warning('Failed imports:');
            $io->listing($failed);
        }

        $io->note([
            'Icons are licensed under CC BY 3.0',
            'Attribution: game-icons.net',
            'Individual artists credited in source field',
        ]);

        return Command::SUCCESS;
    }

    private function prepareRepository(SymfonyStyle $io): bool
    {
        if (is_dir(self::TEMP_DIR)) {
            $io->text('Repository already exists, updating...');
            exec('cd ' . self::TEMP_DIR . ' && git pull origin master 2>&1', $output, $returnCode);

            return 0 === $returnCode;
        }

        $io->text('Cloning repository (this may take a minute)...');
        exec('git clone --depth 1 ' . self::REPO_URL . ' ' . self::TEMP_DIR . ' 2>&1', $output, $returnCode);

        return 0 === $returnCode;
    }

    private function findIconInRepo(string $filename): ?string
    {
        // Search through all artist directories for this icon
        $dirs = glob(self::TEMP_DIR . '/*', GLOB_ONLYDIR);

        foreach ($dirs as $dir) {
            $iconPath = $dir . '/' . $filename . '.svg';
            if (file_exists($iconPath)) {
                return $iconPath;
            }
        }

        return null;
    }

    private function getArtistFromPath(string $path): string
    {
        // Extract artist name from path: /tmp/game-icons-download/{artist}/icon.svg
        $parts = explode('/', $path);

        return $parts[count($parts) - 2] ?? 'unknown';
    }
}
