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
        ['musket', 'weapon', 'Rifle', ['firearm', 'ranged', 'weapon']], // Better rifle icon than winchester-rifle
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

        // Additional Weapons
        ['battle-axe', 'weapon', 'Battle Axe', ['melee', 'weapon']],
        ['flail', 'weapon', 'Flail', ['melee', 'weapon']],
        ['halberd', 'weapon', 'Halberd', ['melee', 'weapon']],
        ['rapier', 'weapon', 'Rapier', ['melee', 'blade', 'weapon']],
        ['sabers-choc', 'weapon', 'Saber', ['melee', 'blade', 'weapon']],
        ['thrust', 'weapon', 'Thrust', ['melee', 'weapon']],
        ['reload', 'weapon', 'Reload', ['weapon', 'action']],
        ['revolver', 'weapon', 'Revolver', ['firearm', 'ranged', 'weapon']],
        ['machine-gun', 'weapon', 'Machine Gun', ['firearm', 'ranged', 'weapon']],
        ['flamethrower', 'weapon', 'Flamethrower', ['firearm', 'ranged', 'weapon']],
        ['laser-gun', 'weapon', 'Laser Gun', ['firearm', 'ranged', 'weapon']],
        ['plasma-blast', 'weapon', 'Plasma Gun', ['firearm', 'ranged', 'weapon']],

        // Additional Movement
        ['run', 'movement', 'Run', ['movement', 'action']],
        ['sprint-foot', 'movement', 'Sprint Foot', ['movement', 'action']],
        ['flying-target', 'movement', 'Fly', ['movement', 'action']],
        ['wingfoot', 'movement', 'Winged Boots', ['movement', 'ability']],
        ['teleport', 'movement', 'Teleport', ['movement', 'ability']],
        ['portal', 'movement', 'Portal', ['movement', 'ability']],
        ['falling', 'movement', 'Fall', ['movement', 'action']],
        ['falling-boulder', 'movement', 'Fall Damage', ['movement', 'damage']],

        // Additional Resources & Items
        ['heart-plus', 'resource', 'Extra Health', ['resource', 'health']],
        ['heart-container', 'resource', 'Health Container', ['resource', 'health']],
        ['heart-tower', 'resource', 'Health Tower', ['resource', 'health']],
        ['hearts', 'resource', 'Hearts', ['resource', 'health']],
        ['heart-broken', 'resource', 'Broken Heart', ['resource', 'health', 'damage']],
        ['potion-of-madness', 'resource', 'Madness Potion', ['resource', 'item']],
        ['potion-of-wisdom', 'resource', 'Wisdom Potion', ['resource', 'item']],
        ['bottled-bolt', 'resource', 'Bottled Bolt', ['resource', 'item']],
        ['energy-tank', 'resource', 'Energy Tank', ['resource', 'energy']],
        ['battery-pack', 'resource', 'Battery', ['resource', 'energy']],
        ['battery-0', 'resource', 'Empty Battery', ['resource', 'energy']],
        ['battery-75', 'resource', 'Battery 75%', ['resource', 'energy']],
        ['battery-100', 'resource', 'Full Battery', ['resource', 'energy']],
        ['shield', 'resource', 'Shield', ['resource', 'defense']],
        ['shield-bounces', 'resource', 'Bouncing Shield', ['resource', 'defense']],
        ['shield-reflect', 'resource', 'Reflecting Shield', ['resource', 'defense']],
        ['armor-upgrade', 'resource', 'Armor Upgrade', ['resource', 'defense']],
        ['armor-punch', 'resource', 'Armored Fist', ['resource', 'defense']],
        ['gem-pendant', 'resource', 'Gem', ['resource', 'item']],
        ['gem-necklace', 'resource', 'Gem Necklace', ['resource', 'item']],
        ['diamond', 'resource', 'Diamond', ['resource', 'currency']],
        ['money-stack', 'resource', 'Money Stack', ['resource', 'currency']],
        ['coins', 'resource', 'Coins', ['resource', 'currency']],
        ['cheese', 'resource', 'Cheese', ['resource', 'food']],
        ['apple', 'resource', 'Apple', ['resource', 'food']],
        ['bread', 'resource', 'Bread', ['resource', 'food']],
        ['carrot', 'resource', 'Carrot', ['resource', 'food']],
        ['fish', 'resource', 'Fish', ['resource', 'food']],
        ['canned-fish', 'resource', 'Canned Fish', ['resource', 'food']],

        // Additional Combat & Status
        ['sword-clash', 'combat', 'Sword Clash', ['combat']],
        ['sword-slice', 'combat', 'Sword Slice', ['combat', 'melee']],
        ['sword-spade', 'combat', 'Sword Spade', ['combat', 'melee']],
        ['sword-wound', 'combat', 'Sword Wound', ['combat', 'damage']],
        ['sword-man', 'combat', 'Swordsman', ['combat', 'melee']],
        ['punch-blast', 'combat', 'Punch Blast', ['combat', 'melee']],
        ['punch', 'combat', 'Punch', ['combat', 'melee']],
        ['fist', 'combat', 'Fist', ['combat', 'melee']],
        ['fist-bump', 'combat', 'Fist Bump', ['combat', 'melee']],
        ['kick', 'combat', 'Kick', ['combat', 'melee']],
        ['kick-scooter', 'combat', 'Kick Scooter', ['combat', 'melee']],
        ['claw-slashes', 'combat', 'Claw Slashes', ['combat', 'melee']],
        ['claw-string', 'combat', 'Claw String', ['combat', 'melee']],
        ['target-laser', 'combat', 'Target Laser', ['combat', 'aiming']],
        ['target-arrows', 'combat', 'Target Arrows', ['combat', 'aiming']],
        ['targeting', 'combat', 'Targeting', ['combat', 'aiming']],
        ['crosshair', 'combat', 'Crosshair', ['combat', 'aiming']],
        ['crosshair-arrow', 'combat', 'Crosshair Arrow', ['combat', 'aiming']],
        ['shield-bash', 'combat', 'Shield Bash', ['combat', 'defense']],
        ['shield-reflect', 'combat', 'Shield Reflect', ['combat', 'defense']],
        ['shield-echoes', 'combat', 'Shield Echoes', ['combat', 'defense']],
        ['skull-crossed-bones', 'combat', 'Skull Crossbones', ['combat', 'death']],
        ['skull-crack', 'combat', 'Skull Crack', ['combat', 'death']],
        ['skull-in-jar', 'combat', 'Skull in Jar', ['combat', 'death']],
        ['skull-shield', 'combat', 'Skull Shield', ['combat', 'death']],
        ['bleeding-wound', 'combat', 'Bleeding Wound', ['combat', 'damage']],
        ['bleeding-eye', 'combat', 'Bleeding Eye', ['combat', 'damage']],
        ['bloody-stash', 'combat', 'Bloody Stash', ['combat', 'damage']],
        ['bloody-sword', 'combat', 'Bloody Sword', ['combat', 'damage']],
        ['blood-drop', 'combat', 'Blood Drop', ['combat', 'damage']],
        ['bloody-claw', 'combat', 'Bloody Claw', ['combat', 'damage']],
        ['stiletto', 'combat', 'Stiletto', ['combat', 'stealth']],
        ['backstab', 'combat', 'Backstab', ['combat', 'stealth']],
        ['stealth', 'combat', 'Stealth', ['combat', 'stealth']],
        ['ninja-mask', 'combat', 'Ninja Mask', ['combat', 'stealth']],
        ['ninja-star', 'combat', 'Ninja Star', ['combat', 'stealth']],

        // Additional Magic & Abilities
        ['magic-lamp', 'ability', 'Magic Lamp', ['magic', 'ability']],
        ['magic-potion', 'ability', 'Magic Potion', ['magic', 'ability']],
        ['magic-shield', 'ability', 'Magic Shield', ['magic', 'defense']],
        ['magic-swirl', 'ability', 'Magic Swirl', ['magic', 'ability']],
        ['magic-trident', 'ability', 'Magic Trident', ['magic', 'weapon']],
        ['spell-book', 'ability', 'Spell Book', ['magic', 'spell']],
        ['spell-book-2', 'ability', 'Spell Book 2', ['magic', 'spell']],
        ['scroll-unfurled', 'ability', 'Scroll', ['magic', 'spell']],
        ['fireball', 'ability', 'Fireball', ['magic', 'spell', 'fire']],
        ['fire-bowl', 'ability', 'Fire Bowl', ['magic', 'spell', 'fire']],
        ['fire-flame', 'ability', 'Fire Flame', ['magic', 'spell', 'fire']],
        ['fire-ring', 'ability', 'Fire Ring', ['magic', 'spell', 'fire']],
        ['fire-shield', 'ability', 'Fire Shield', ['magic', 'spell', 'fire']],
        ['ice-bolt', 'ability', 'Ice Bolt', ['magic', 'spell', 'ice']],
        ['ice-cube', 'ability', 'Ice Cube', ['magic', 'spell', 'ice']],
        ['ice-shield', 'ability', 'Ice Shield', ['magic', 'spell', 'ice']],
        ['ice-spell-cast', 'ability', 'Ice Spell Cast', ['magic', 'spell', 'ice']],
        ['lightning-bolt', 'ability', 'Lightning Bolt', ['magic', 'spell', 'lightning']],
        ['lightning-storm', 'ability', 'Lightning Storm', ['magic', 'spell', 'lightning']],
        ['lightning-sword', 'ability', 'Lightning Sword', ['magic', 'spell', 'lightning']],
        ['lightning-tree', 'ability', 'Lightning Tree', ['magic', 'spell', 'lightning']],
        ['round-star', 'ability', 'Star', ['ability', 'ultimate']],
        ['star-pupil', 'ability', 'Star Pupil', ['ability', 'ultimate']],
        ['star-prominences', 'ability', 'Star Prominences', ['ability', 'ultimate']],
        ['star-struck', 'ability', 'Star Struck', ['ability', 'ultimate']],

        // Additional Strategy & MOBA
        ['tower', 'strategy', 'Tower', ['strategy', 'building']],
        ['watchtower', 'strategy', 'Watchtower', ['strategy', 'building']],
        ['tower-flag', 'strategy', 'Tower Flag', ['strategy', 'building']],
        ['castle-ruins', 'strategy', 'Castle Ruins', ['strategy', 'building']],
        ['castle-2', 'strategy', 'Castle 2', ['strategy', 'building']],
        ['fortress', 'strategy', 'Fortress', ['strategy', 'building']],
        ['barracks-tent', 'strategy', 'Barracks Tent', ['strategy', 'building']],
        ['mining', 'strategy', 'Mining', ['strategy', 'resource']],
        ['mining-diamond', 'strategy', 'Mining Diamond', ['strategy', 'resource']],
        ['mining-helmet', 'strategy', 'Mining Helmet', ['strategy', 'resource']],
        ['upgrade', 'strategy', 'Upgrade', ['strategy', 'progression']],
        ['upgrade-arrow', 'strategy', 'Upgrade Arrow', ['strategy', 'progression']],
        ['upgrade-point', 'strategy', 'Upgrade Point', ['strategy', 'progression']],
        ['chess-pawn', 'strategy', 'Chess Pawn', ['strategy', 'unit']],
        ['chess-king', 'strategy', 'Chess King', ['strategy', 'unit']],
        ['chess-queen', 'strategy', 'Chess Queen', ['strategy', 'unit']],
        ['chess-knight', 'strategy', 'Chess Knight', ['strategy', 'unit']],
        ['chess-rook', 'strategy', 'Chess Rook', ['strategy', 'unit']],
        ['chess-bishop', 'strategy', 'Chess Bishop', ['strategy', 'unit']],

        // Additional Survival & Crafting
        ['campfire', 'survival', 'Campfire', ['survival', 'crafting']],
        ['bonfire', 'survival', 'Bonfire', ['survival', 'crafting']],
        ['fire-pit', 'survival', 'Fire Pit', ['survival', 'crafting']],
        ['wood-pile', 'survival', 'Wood Pile', ['survival', 'resource']],
        ['log', 'survival', 'Log', ['survival', 'resource']],
        ['tree-branch', 'survival', 'Tree Branch', ['survival', 'resource']],
        ['stone-pile', 'survival', 'Stone Pile', ['survival', 'resource']],
        ['stone-block', 'survival', 'Stone Block', ['survival', 'resource']],
        ['meat', 'survival', 'Meat', ['survival', 'food']],
        ['meat-cleaver', 'survival', 'Meat Cleaver', ['survival', 'food']],
        ['meat-hook', 'survival', 'Meat Hook', ['survival', 'food']],
        ['water-drop', 'survival', 'Water Drop', ['survival', 'resource']],
        ['water-bottle', 'survival', 'Water Bottle', ['survival', 'resource']],
        ['thermometer', 'survival', 'Thermometer', ['survival', 'status']],
        ['thermometer-cold', 'survival', 'Thermometer Cold', ['survival', 'status']],
        ['thermometer-hot', 'survival', 'Thermometer Hot', ['survival', 'status']],

        // Additional UI & Modifiers
        ['skills', 'modifier', 'Skills', ['modifier', 'progression']],
        ['skill-upgrade', 'modifier', 'Skill Upgrade', ['modifier', 'progression']],
        ['locked-chest', 'modifier', 'Locked Chest', ['modifier', 'restriction']],
        ['locked-door', 'modifier', 'Locked Door', ['modifier', 'restriction']],
        ['padlock', 'modifier', 'Padlock', ['modifier', 'restriction']],
        ['save', 'modifier', 'Save', ['modifier', 'action']],
        ['save-arrow', 'modifier', 'Save Arrow', ['modifier', 'action']],
        ['load', 'modifier', 'Load', ['modifier', 'action']],
        ['treasure-map', 'modifier', 'Treasure Map', ['modifier', 'navigation']],
        ['map', 'modifier', 'Map', ['modifier', 'navigation']],
        ['compass', 'modifier', 'Compass', ['modifier', 'navigation']],
        ['compass-rose', 'modifier', 'Compass Rose', ['modifier', 'navigation']],
        ['fast-forward-button', 'modifier', 'Fast Forward', ['modifier', 'movement']],
        ['conversation', 'modifier', 'Conversation', ['modifier', 'action']],
        ['chat-bubble', 'modifier', 'Chat Bubble', ['modifier', 'action']],
        ['shopping-cart', 'modifier', 'Shopping Cart', ['modifier', 'action']],
        ['shopping-bag', 'modifier', 'Shopping Bag', ['modifier', 'action']],
        ['timer', 'modifier', 'Timer', ['modifier', 'time']],
        ['hourglass', 'modifier', 'Hourglass', ['modifier', 'time']],
        ['stopwatch', 'modifier', 'Stopwatch', ['modifier', 'time']],
        ['clockwork', 'modifier', 'Clockwork', ['modifier', 'time']],
        ['counter', 'modifier', 'Counter', ['modifier', 'count']],
        ['number-1', 'modifier', 'Number 1', ['modifier', 'count']],
        ['number-2', 'modifier', 'Number 2', ['modifier', 'count']],
        ['number-3', 'modifier', 'Number 3', ['modifier', 'count']],
        ['number-4', 'modifier', 'Number 4', ['modifier', 'count']],
        ['number-5', 'modifier', 'Number 5', ['modifier', 'count']],
        ['prohibited', 'modifier', 'Prohibited', ['modifier', 'restriction']],
        ['forbidden', 'modifier', 'Forbidden', ['modifier', 'restriction']],
        ['no-entry', 'modifier', 'No Entry', ['modifier', 'restriction']],

        // Additional Horror & Atmosphere
        ['bleeding-eye', 'horror', 'Bleeding Eye', ['horror', 'atmosphere']],
        ['evil-eye', 'horror', 'Evil Eye', ['horror', 'atmosphere']],
        ['eye-of-horus', 'horror', 'Eye of Horus', ['horror', 'atmosphere']],
        ['bat', 'horror', 'Bat', ['horror', 'enemy']],
        ['bat-wing', 'horror', 'Bat Wing', ['horror', 'enemy']],
        ['chainsaw', 'horror', 'Chainsaw', ['horror', 'weapon']],
        ['flashlight', 'horror', 'Flashlight', ['horror', 'item']],
        ['lantern-flame', 'horror', 'Lantern', ['horror', 'item']],
        ['candle-flame', 'horror', 'Candle', ['horror', 'item']],
        ['tombstone', 'horror', 'Tombstone', ['horror', 'atmosphere']],
        ['grave', 'horror', 'Grave', ['horror', 'atmosphere']],
        ['coffin', 'horror', 'Coffin', ['horror', 'atmosphere']],

        // Additional RPG Specific
        ['spell-book', 'rpg', 'Spell Book', ['rpg', 'magic']],
        ['book-cover', 'rpg', 'Book Cover', ['rpg', 'magic']],
        ['book-aura', 'rpg', 'Book Aura', ['rpg', 'magic']],
        ['scroll-unfurled', 'rpg', 'Scroll', ['rpg', 'magic']],
        ['scroll-quill', 'rpg', 'Scroll Quill', ['rpg', 'magic']],
        ['knapsack', 'rpg', 'Knapsack', ['rpg', 'item']],
        ['backpack', 'rpg', 'Backpack', ['rpg', 'item']],
        ['bag', 'rpg', 'Bag', ['rpg', 'item']],
        ['treasure-chest', 'rpg', 'Treasure Chest', ['rpg', 'item']],
        ['chest', 'rpg', 'Chest', ['rpg', 'item']],
        ['laurels', 'rpg', 'Laurels', ['rpg', 'progression']],
        ['trophy', 'rpg', 'Trophy', ['rpg', 'progression']],
        ['trophy-cup', 'rpg', 'Trophy Cup', ['rpg', 'progression']],
        ['achievement', 'rpg', 'Achievement', ['rpg', 'progression']],
        ['level-up', 'rpg', 'Level Up', ['rpg', 'progression']],
        ['level-up-alt', 'rpg', 'Level Up Alt', ['rpg', 'progression']],
        ['exp', 'rpg', 'Experience', ['rpg', 'progression']],
        ['experience', 'rpg', 'Experience Points', ['rpg', 'progression']],
    ];

    private const REPO_URL = 'https://github.com/game-icons/icons.git';
    private const TEMP_DIR = '/tmp/game-icons-download';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private readonly RuleIconRepository $ruleIconRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('skip-existing', null, InputOption::VALUE_NONE, 'Skip icons that already exist in database')
            ->addOption('update-existing', null, InputOption::VALUE_NONE, 'Update existing icons with new SVG data')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Download ALL icons from game-icons.net (not just curated list)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $skipExisting = $input->getOption('skip-existing');
        $updateExisting = $input->getOption('update-existing');
        $downloadAll = $input->getOption('all');

        // Step 1: Clone/update the repository
        $io->section('Step 1: Fetching game-icons repository');
        if (!$this->prepareRepository($io)) {
            $io->error('Failed to fetch repository. Make sure git is installed.');

            return Command::FAILURE;
        }

        // Step 2: Get icon list (curated or all)
        if ($downloadAll) {
            $io->title('Downloading ALL Icons from game-icons.net GitHub');
            $iconsToDownload = $this->discoverAllIcons();
            $io->text([
                'This will download ' . count($iconsToDownload) . ' icons from game-icons.net',
                'Source: https://github.com/game-icons/icons',
                'License: CC BY 3.0',
                'Attribution: Various artists from game-icons.net',
            ]);
        } else {
            $io->title('Downloading Curated Gaming Icons from game-icons.net GitHub');
            $iconsToDownload = array_map(function($iconData) {
                [$filename, $category, $displayName, $tags] = $iconData;
                return [
                    'identifier' => $filename,
                    'category' => $category,
                    'displayName' => $displayName,
                    'tags' => $tags,
                ];
            }, self::ICON_LIST);
            $io->text([
                'This will download ' . count($iconsToDownload) . ' curated gaming icons',
                'Source: https://github.com/game-icons/icons',
                'License: CC BY 3.0',
                'Attribution: Various artists from game-icons.net',
                '',
                'Tip: Use --all to download ALL icons from game-icons.net',
            ]);
        }

        // Step 3: Import icons
        $io->section('Step 2: Importing icons into database');
        $io->progressStart(count($iconsToDownload));

        $downloaded = 0;
        $skipped = 0;
        $updated = 0;
        $failed = [];

        foreach ($iconsToDownload as $iconData) {
            $identifier = $iconData['identifier'];
            $category = $iconData['category'];
            $displayName = $iconData['displayName'];
            $tags = $iconData['tags'];

            $existingIcon = $this->ruleIconRepository->findOneBy(['identifier' => $identifier]);

            if ($existingIcon && $skipExisting && !$updateExisting) {
                ++$skipped;
                $io->progressAdvance();
                continue;
            }

            try {
                // Find the icon file in the repository
                $svgPath = $this->findIconInRepo($identifier);

                if (!$svgPath) {
                    $failed[] = sprintf('%s: File not found in repository', $identifier);
                    $io->progressAdvance();
                    continue;
                }

                $svgContent = file_get_contents($svgPath);
                if ($svgContent === false) {
                    $failed[] = sprintf('%s: Failed to read file', $identifier);
                    $io->progressAdvance();
                    continue;
                }

                $artist = $this->getArtistFromPath($svgPath);

                // Clean and optimize SVG content
                $svgContent = $this->cleanSvgContent($svgContent);

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
                    $this->safeFlush();
                    // Clear entity manager periodically to prevent memory issues
                    $this->entityManager->clear();
                }
            } catch (\Exception $e) {
                $failed[] = sprintf('%s: %s', $identifier, $e->getMessage());
                // If EntityManager is closed, reset it
                if (!$this->entityManager->isOpen()) {
                    $this->resetEntityManager();
                }
            }

            $io->progressAdvance();
        }

        // Final flush
        $this->safeFlush();
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

        if ($dirs === false) {
            return null;
        }

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

    private function cleanSvgContent(string $svgContent): string
    {
        // Remove XML declaration if present
        $cleaned = (string) preg_replace('/<\?xml[^?]*\?>/', '', $svgContent);

        // Remove background rectangles (common in game-icons.net SVGs)
        // Pattern: <path d="M0 0h512v512H0z"></path> - full viewport background
        // This regex matches: M0 0 (or M0,0) followed by h[number]v[number]H0z
        $cleaned = (string) preg_replace('/<path\s+[^>]*d=["\'][Mm]\s*0\s*[, ]?\s*0\s*[hH]\s*[0-9]+\s*[vV]\s*[0-9]+\s*[Hh]\s*0\s*[zZ]["\'][^>]*\/?>/i', '', $cleaned);
        
        // Also remove rect elements that cover the full viewport
        $cleaned = (string) preg_replace('/<rect[^>]*x=["\']0["\'][^>]*y=["\']0["\'][^>]*width=["\'][0-9]+["\'][^>]*height=["\'][0-9]+["\'][^>]*\/?>/i', '', $cleaned);
        
        // Remove paths with just background fill (no actual icon content)
        // Pattern: paths that are just rectangles covering the viewport
        $cleaned = (string) preg_replace('/<path\s+d=["\']M\s*0[^"\']*[hH]\s*[0-9]+[^"\']*[vV]\s*[0-9]+[^"\']*[Hh]\s*0[^"\']*[zZ]["\'][^>]*\/?>/i', '', $cleaned);
        
        // Remove background circles (common in some icons like crosshair/ads)
        // Pattern: circles that cover most/all of the viewport (radius >= 50% of viewBox)
        // Match circles with radius >= 100 (assuming 256x256 viewBox) or similar large circles
        $cleaned = (string) preg_replace('/<circle\s+[^>]*cx=["\']128["\'][^>]*cy=["\']128["\'][^>]*r=["\'](12[0-9]|1[3-9][0-9]|[2-9][0-9]{2})["\'][^>]*\/?>/i', '', $cleaned);
        // Also match circles centered at 50% (256/2 = 128, 512/2 = 256, etc.)
        $cleaned = (string) preg_replace('/<circle\s+[^>]*cx=["\']256["\'][^>]*cy=["\']256["\'][^>]*r=["\'](2[4-9][0-9]|[3-9][0-9]{2})["\'][^>]*\/?>/i', '', $cleaned);
        $cleaned = (string) preg_replace('/<circle\s+[^>]*cx=["\']512["\'][^>]*cy=["\']512["\'][^>]*r=["\'](4[8-9][0-9]|[5-9][0-9]{2})["\'][^>]*\/?>/i', '', $cleaned);
        
        // Remove circles with stroke that are clearly backgrounds (large radius, white/black stroke)
        $cleaned = (string) preg_replace('/<circle\s+[^>]*stroke=["\']#?fff(fff)?["\'][^>]*r=["\'](10[0-9]|1[1-9][0-9]|[2-9][0-9]{2})["\'][^>]*\/?>/i', '', $cleaned);
        $cleaned = (string) preg_replace('/<circle\s+[^>]*stroke=["\']white["\'][^>]*r=["\'](10[0-9]|1[1-9][0-9]|[2-9][0-9]{2})["\'][^>]*\/?>/i', '', $cleaned);

        // Replace hardcoded fill colors with currentColor
        // Replace fill="#fff", fill="#ffffff", fill="white", etc. with fill="currentColor"
        $cleaned = (string) preg_replace('/fill=["\']#?fff(fff)?["\']/i', 'fill="currentColor"', $cleaned);
        $cleaned = (string) preg_replace('/fill=["\']white["\']/i', 'fill="currentColor"', $cleaned);
        $cleaned = (string) preg_replace('/fill=["\']#000(000)?["\']/i', 'fill="currentColor"', $cleaned);
        $cleaned = (string) preg_replace('/fill=["\']black["\']/i', 'fill="currentColor"', $cleaned);
        
        // Replace hardcoded stroke colors with currentColor (for non-background elements)
        $cleaned = (string) preg_replace('/stroke=["\']#?fff(fff)?["\']/i', 'stroke="currentColor"', $cleaned);
        $cleaned = (string) preg_replace('/stroke=["\']white["\']/i', 'stroke="currentColor"', $cleaned);
        $cleaned = (string) preg_replace('/stroke=["\']#000(000)?["\']/i', 'stroke="currentColor"', $cleaned);
        $cleaned = (string) preg_replace('/stroke=["\']black["\']/i', 'stroke="currentColor"', $cleaned);
        
        // Remove any fill="none" on main paths (they should have fill)
        $cleaned = (string) preg_replace('/<path\s+([^>]*)\s+fill=["\']none["\']([^>]*)>/', '<path $1$2>', $cleaned);

        // Ensure SVG has viewBox attribute for proper scaling
        if (strpos($cleaned, 'viewBox') === false) {
            // Try to extract width and height to create viewBox
            if (preg_match('/width="([\d.]+)"/', $cleaned, $widthMatch)
                && preg_match('/height="([\d.]+)"/', $cleaned, $heightMatch)) {
                $width = $widthMatch[1];
                $height = $heightMatch[1];
                $cleaned = (string) preg_replace(
                    '/<svg/',
                    sprintf('<svg viewBox="0 0 %s %s"', $width, $height),
                    $cleaned,
                    1
                );
            } else {
                // Default viewBox for game-icons.net (usually 512x512)
                $cleaned = (string) preg_replace('/<svg/', '<svg viewBox="0 0 512 512"', $cleaned, 1);
            }
        }

        // Remove width and height attributes to allow CSS sizing
        $cleaned = (string) preg_replace('/<svg([^>]*)\s+(width|height)="[^"]*"/', '<svg$1', $cleaned);
        $cleaned = (string) preg_replace('/<svg([^>]*)\s+(width|height)="[^"]*"/', '<svg$1', $cleaned); // Run twice to remove both

        // Ensure all paths have fill="currentColor" if they don't have a fill attribute
        // This helps with icons that rely on default fill behavior
        $cleaned = (string) preg_replace('/<path\s+((?!fill=)[^>]*?)>/', '<path fill="currentColor" $1>', $cleaned);

        // Trim whitespace
        return trim($cleaned);
    }

    private function discoverAllIcons(): array
    {
        $icons = [];
        $dirs = glob(self::TEMP_DIR . '/*', GLOB_ONLYDIR);

        if ($dirs === false) {
            return [];
        }

        foreach ($dirs as $dir) {
            $artist = basename($dir);
            if ($artist === '.git') {
                continue;
            }

            $svgFiles = glob($dir . '/*.svg');
            if ($svgFiles === false) {
                continue;
            }

            foreach ($svgFiles as $svgFile) {
                $filename = basename($svgFile, '.svg');
                
                // Check if this icon is in curated list (use curated metadata if available)
                $inCuratedList = false;
                $curatedData = null;
                foreach (self::ICON_LIST as $curatedIcon) {
                    if ($curatedIcon[0] === $filename) {
                        $inCuratedList = true;
                        $curatedData = $curatedIcon;
                        break;
                    }
                }
                
                if ($inCuratedList && $curatedData !== null) {
                    // Use curated metadata (better category/display name/tags)
                    [$curatedFilename, $category, $displayName, $tags] = $curatedData;
                    $icons[] = [
                        'identifier' => $curatedFilename,
                        'category' => $category,
                        'displayName' => $displayName,
                        'tags' => $tags,
                    ];
                } else {
                    // Infer category and generate display name for non-curated icons
                    $category = $this->inferCategory($filename);
                    $displayName = $this->generateDisplayName($filename);
                    $tags = $this->generateTags($filename, $category);
                    
                    $icons[] = [
                        'identifier' => $filename,
                        'category' => $category,
                        'displayName' => $displayName,
                        'tags' => $tags,
                    ];
                }
            }
        }

        // Sort by identifier
        usort($icons, fn($a, $b) => strcmp($a['identifier'], $b['identifier']));

        return $icons;
    }

    private function inferCategory(string $filename): string
    {
        $filenameLower = strtolower($filename);

        // Weapon keywords
        $weaponKeywords = ['sword', 'axe', 'mace', 'dagger', 'knife', 'pistol', 'gun', 'rifle', 'bow', 'crossbow', 'spear', 'club', 'grenade', 'rocket', 'mine', 'blade', 'halberd', 'rapier', 'flail', 'revolver', 'shotgun', 'sniper', 'laser', 'plasma', 'flame'];
        foreach ($weaponKeywords as $keyword) {
            if (strpos($filenameLower, $keyword) !== false) {
                return 'weapon';
            }
        }

        // Movement keywords
        $movementKeywords = ['walk', 'run', 'sprint', 'jump', 'dodge', 'crouch', 'dash', 'climb', 'fly', 'teleport', 'portal', 'fall'];
        foreach ($movementKeywords as $keyword) {
            if (strpos($filenameLower, $keyword) !== false) {
                return 'movement';
            }
        }

        // Resource keywords
        $resourceKeywords = ['health', 'heart', 'potion', 'ammo', 'shield', 'armor', 'key', 'gold', 'coin', 'med', 'battery', 'energy', 'gem', 'diamond', 'food', 'meat', 'water', 'apple', 'bread'];
        foreach ($resourceKeywords as $keyword) {
            if (strpos($filenameLower, $keyword) !== false) {
                return 'resource';
            }
        }

        // Combat keywords
        $combatKeywords = ['punch', 'attack', 'target', 'crosshair', 'skull', 'death', 'damage', 'bleed', 'blood', 'wound', 'fist', 'kick', 'claw', 'stealth', 'ninja', 'backstab'];
        foreach ($combatKeywords as $keyword) {
            if (strpos($filenameLower, $keyword) !== false) {
                return 'combat';
            }
        }

        // Magic/Ability keywords
        $magicKeywords = ['magic', 'wand', 'spell', 'fireball', 'ice', 'lightning', 'crystal', 'fire', 'frost', 'bolt', 'star'];
        foreach ($magicKeywords as $keyword) {
            if (strpos($filenameLower, $keyword) !== false) {
                return 'ability';
            }
        }

        // Survival keywords
        $survivalKeywords = ['campfire', 'wood', 'stone', 'meat', 'water', 'food', 'thermometer', 'temperature'];
        foreach ($survivalKeywords as $keyword) {
            if (strpos($filenameLower, $keyword) !== false) {
                return 'survival';
            }
        }

        // RPG keywords
        $rpgKeywords = ['spell', 'scroll', 'treasure', 'chest', 'knapsack', 'inventory', 'backpack', 'trophy', 'achievement', 'level', 'exp', 'experience'];
        foreach ($rpgKeywords as $keyword) {
            if (strpos($filenameLower, $keyword) !== false) {
                return 'rpg';
            }
        }

        // Strategy keywords
        $strategyKeywords = ['tower', 'castle', 'barracks', 'mining', 'upgrade', 'chess', 'pawn', 'king', 'queen', 'knight', 'rook', 'bishop'];
        foreach ($strategyKeywords as $keyword) {
            if (strpos($filenameLower, $keyword) !== false) {
                return 'strategy';
            }
        }

        // Horror keywords
        $horrorKeywords = ['bat', 'chainsaw', 'flashlight', 'lantern', 'candle', 'tombstone', 'grave', 'coffin', 'evil', 'eye'];
        foreach ($horrorKeywords as $keyword) {
            if (strpos($filenameLower, $keyword) !== false) {
                return 'horror';
            }
        }

        // Modifier/UI keywords
        $modifierKeywords = ['save', 'load', 'map', 'compass', 'shop', 'timer', 'hourglass', 'stopwatch', 'counter', 'number', 'lock', 'prohibited', 'forbidden', 'skill'];
        foreach ($modifierKeywords as $keyword) {
            if (strpos($filenameLower, $keyword) !== false) {
                return 'modifier';
            }
        }

        return 'other';
    }

    private function generateDisplayName(string $identifier): string
    {
        // Convert kebab-case to Title Case
        $words = explode('-', $identifier);
        $words = array_map('ucfirst', $words);

        return implode(' ', $words);
    }

    private function generateTags(string $identifier, string $category): array
    {
        $tags = [$category];
        $identifierLower = strtolower($identifier);

        // Add relevant tags based on identifier
        if (strpos($identifierLower, 'weapon') !== false || strpos($identifierLower, 'sword') !== false || strpos($identifierLower, 'gun') !== false) {
            $tags[] = 'weapon';
        }
        if (strpos($identifierLower, 'magic') !== false || strpos($identifierLower, 'spell') !== false) {
            $tags[] = 'magic';
        }
        if (strpos($identifierLower, 'health') !== false || strpos($identifierLower, 'heart') !== false) {
            $tags[] = 'health';
        }
        if (strpos($identifierLower, 'movement') !== false || strpos($identifierLower, 'walk') !== false || strpos($identifierLower, 'run') !== false) {
            $tags[] = 'movement';
        }
        if (strpos($identifierLower, 'combat') !== false || strpos($identifierLower, 'attack') !== false) {
            $tags[] = 'combat';
        }

        return array_unique($tags);
    }

    private function safeFlush(): void
    {
        try {
            if ($this->entityManager->isOpen()) {
                $this->entityManager->flush();
            } else {
                $this->resetEntityManager();
            }
        } catch (\Exception $e) {
            // If flush fails, reset EntityManager and continue
            $this->resetEntityManager();
        }
    }

    private function resetEntityManager(): void
    {
        if (!$this->entityManager->isOpen()) {
            // Get a new EntityManager instance from the container
            $container = $this->getApplication()->getKernel()->getContainer();
            $this->entityManager = $container->get('doctrine.orm.entity_manager');
        }
    }
}
