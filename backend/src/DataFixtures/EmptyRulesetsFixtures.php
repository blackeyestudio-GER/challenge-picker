<?php

namespace App\DataFixtures;

use App\Entity\Game;
use App\Entity\Rule;
use App\Entity\RuleDifficultyLevel;
use App\Entity\Ruleset;
use App\Entity\RulesetRuleCard;
use App\Entity\TarotCard;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EmptyRulesetsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // First, create all game-specific rules that don't exist yet
        $this->createGameSpecificRules($manager);

        // Then attach rules to all empty rulesets
        $this->attachRulesToRulesets($manager);

        $manager->flush();
    }

    private function createGameSpecificRules(ObjectManager $manager): void
    {
        $newRules = [
            // Zelda-specific rules
            ['name' => 'No Extra Hearts', 'type' => 'legendary', 'desc' => 'Cannot collect heart containers or pieces. Stay at starting health.'],
            ['name' => 'No Bottles', 'type' => 'legendary', 'desc' => 'Cannot use bottles for storing items or fairies.'],
            ['name' => 'No Fairy Revival', 'type' => 'legendary', 'desc' => 'Cannot use fairies for automatic revival.'],
            ['name' => 'No Sword', 'type' => 'legendary', 'desc' => 'Cannot use any sword. Use alternative weapons only.'],
            ['name' => 'No Transformation Masks', 'type' => 'legendary', 'desc' => 'Cannot use Deku, Goron, or Zora masks. Human form only.'],
            ['name' => 'No Wolf Form', 'type' => 'legendary', 'desc' => 'Cannot transform into wolf when optional. Human form only.'],
            ['name' => 'No Armor Upgrades', 'type' => 'legendary', 'desc' => 'Cannot upgrade armor. Base defense only.'],
            ['name' => 'No Guardian Acorns', 'type' => 'legendary', 'desc' => 'Cannot use Guardian Acorns or Pieces of Power for upgrades.'],
            ['name' => 'No Tingle Tuner', 'type' => 'legendary', 'desc' => 'Cannot use helper items or external assistance.'],
            ['name' => 'No Sheikah Slate', 'type' => 'legendary', 'desc' => 'Cannot use Sheikah Slate abilities. No bombs, stasis, or magnesis.'],
            ['name' => 'No Fuse', 'type' => 'legendary', 'desc' => 'Cannot use the Fuse ability. Base weapons only.'],
            ['name' => 'No Healing Food', 'type' => 'legendary', 'desc' => 'Cannot cook or eat healing food. Natural healing only.'],
            ['name' => 'No Potions', 'type' => 'legendary', 'desc' => 'Cannot use potions for healing or buffs.'],

            // Roguelike-specific rules
            ['name' => 'No Boons', 'type' => 'legendary', 'desc' => 'Cannot accept god boons. Base stats only.'],
            ['name' => 'No Mirror Upgrades', 'type' => 'legendary', 'desc' => 'Cannot use Mirror of Night permanent upgrades.'],
            ['name' => 'No Arcana Cards', 'type' => 'legendary', 'desc' => 'Cannot use Arcana card buffs.'],
            ['name' => 'No Damage Items', 'type' => 'legendary', 'desc' => 'Cannot pick up items that increase damage stat.'],
            ['name' => 'No Tears', 'type' => 'legendary', 'desc' => 'Cannot shoot tears. Bombs and orbitals only.'],
            ['name' => 'No Soul Hearts', 'type' => 'legendary', 'desc' => 'Can only use red hearts. No soul or black hearts.'],
            ['name' => 'No Card Additions', 'type' => 'legendary', 'desc' => 'Cannot add cards to deck. Starting deck only.'],
            ['name' => 'No Rare Cards', 'type' => 'legendary', 'desc' => 'Cannot pick rare rarity cards. Commons and uncommons only.'],
            ['name' => 'Minimize Deck', 'type' => 'legendary', 'desc' => 'Must remove cards at every opportunity. Smallest deck possible.'],
            ['name' => 'No Jokers', 'type' => 'legendary', 'desc' => 'Cannot add jokers to deck.'],
            ['name' => 'One Hand Type', 'type' => 'legendary', 'desc' => 'Can only score with one poker hand type.'],

            // Puzzle-specific rules
            ['name' => 'No Guides', 'type' => 'legendary', 'desc' => 'Cannot look up puzzle solutions. Pure logic only.'],
            ['name' => 'Random Order', 'type' => 'legendary', 'desc' => 'Must complete areas in non-optimal order.'],
            ['name' => 'Speedrun Mode', 'type' => 'legendary', 'desc' => 'Must complete within strict time limit.'],

            // Category-specific rules
            ['name' => 'No Backtracking', 'type' => 'legendary', 'desc' => 'Once you leave an area, you cannot return.'],
            ['name' => 'No Rerolls', 'type' => 'legendary', 'desc' => 'Cannot reroll items or abilities. Accept what RNG gives.'],

            // Counter rules
            ['name' => 'Collect Heart', 'type' => 'basic', 'desc' => 'Pick up a heart container or piece.'],
            ['name' => 'Use Bottle', 'type' => 'basic', 'desc' => 'Use a bottle item.'],
            ['name' => 'Use Fairy', 'type' => 'basic', 'desc' => 'Use a fairy for healing or revival.'],
            ['name' => 'Use Sword', 'type' => 'basic', 'desc' => 'Attack with a sword.'],
            ['name' => 'Transform', 'type' => 'basic', 'desc' => 'Change form or transformation.'],
            ['name' => 'Upgrade Armor', 'type' => 'basic', 'desc' => 'Get a defense upgrade.'],
            ['name' => 'Accept Boon', 'type' => 'basic', 'desc' => 'Accept a god boon offer.'],
            ['name' => 'Use Mirror', 'type' => 'basic', 'desc' => 'Activate a Mirror of Night upgrade.'],
            ['name' => 'Pick Damage Item', 'type' => 'basic', 'desc' => 'Collect an item that increases damage.'],
            ['name' => 'Fire Tears', 'type' => 'basic', 'desc' => 'Shoot tears as Isaac.'],
            ['name' => 'Pick Soul Heart', 'type' => 'basic', 'desc' => 'Collect a soul or black heart.'],
            ['name' => 'Add Card', 'type' => 'basic', 'desc' => 'Add a card to your deck.'],
            ['name' => 'Pick Rare Card', 'type' => 'basic', 'desc' => 'Select a rare rarity card.'],
            ['name' => 'Skip Remove', 'type' => 'basic', 'desc' => 'Skip an opportunity to remove a card.'],
            ['name' => 'Add Joker', 'type' => 'basic', 'desc' => 'Add a joker to your deck.'],
            ['name' => 'Score Different Hand', 'type' => 'basic', 'desc' => 'Score with a different poker hand type.'],
            ['name' => 'Look Up Solution', 'type' => 'basic', 'desc' => 'Check a guide or solution.'],
            ['name' => 'Backtrack', 'type' => 'basic', 'desc' => 'Return to a previously visited area.'],
            ['name' => 'Reroll', 'type' => 'basic', 'desc' => 'Reroll an item or ability.'],
        ];

        foreach ($newRules as $ruleData) {
            // Check if rule already exists
            $existingRule = $manager->getRepository(Rule::class)->findOneBy(['name' => $ruleData['name']]);
            if ($existingRule) {
                continue; // Skip if already exists
            }

            $rule = new Rule();
            $rule->setName($ruleData['name']);
            $rule->setDescription($ruleData['desc']);
            $rule->setRuleType($ruleData['type']);

            // Add difficulty levels based on type
            if ($ruleData['type'] === 'basic') {
                for ($level = 1; $level <= 10; ++$level) {
                    $difficulty = new RuleDifficultyLevel();
                    $difficulty->setRule($rule);
                    $difficulty->setDifficultyLevel($level);
                    $difficulty->setAmount($level); // Counter: 1-10 times
                    $difficulty->setDurationSeconds(null);
                    $manager->persist($difficulty);
                }
            }

            $manager->persist($rule);
        }

        $manager->flush();
    }

    private function attachRulesToRulesets(ObjectManager $manager): void
    {
        $rulesetConfigs = [
            // Zelda: Ocarina of Time
            ['name' => 'Zelda OOT: 3 Heart Challenge', 'rules' => [
                ['rule' => 'No Extra Hearts', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Collect Heart', 'card' => 'ace_of_cups', 'pos' => 2, 'default' => false],
                ['rule' => 'Use Bottle', 'card' => 'ace_of_wands', 'pos' => 3, 'default' => false],
                ['rule' => 'Use Fairy', 'card' => 'ace_of_swords', 'pos' => 4, 'default' => false],
            ]],
            ['name' => 'Zelda OOT: No Bottle/Fairy', 'rules' => [
                ['rule' => 'No Bottles', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'No Fairy Revival', 'card' => 'the_magician', 'pos' => 2, 'default' => true],
                ['rule' => 'Use Bottle', 'card' => 'ace_of_cups', 'pos' => 3, 'default' => false],
                ['rule' => 'Use Fairy', 'card' => 'ace_of_swords', 'pos' => 4, 'default' => false],
            ]],
            ['name' => 'Zelda OOT: Swordless', 'rules' => [
                ['rule' => 'No Sword', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Use Sword', 'card' => 'ace_of_swords', 'pos' => 2, 'default' => false],
                ['rule' => 'Use Bottle', 'card' => 'ace_of_cups', 'pos' => 3, 'default' => false],
            ]],

            // Zelda: Majora's Mask
            ['name' => 'Zelda MM: 3 Heart Challenge', 'rules' => [
                ['rule' => 'No Extra Hearts', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Collect Heart', 'card' => 'ace_of_cups', 'pos' => 2, 'default' => false],
                ['rule' => 'Use Bottle', 'card' => 'ace_of_wands', 'pos' => 3, 'default' => false],
            ]],
            ['name' => 'Zelda MM: No Bottle/Fairy', 'rules' => [
                ['rule' => 'No Bottles', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'No Fairy Revival', 'card' => 'the_magician', 'pos' => 2, 'default' => true],
                ['rule' => 'Use Bottle', 'card' => 'ace_of_cups', 'pos' => 3, 'default' => false],
                ['rule' => 'Use Fairy', 'card' => 'ace_of_swords', 'pos' => 4, 'default' => false],
            ]],
            ['name' => 'Zelda MM: No Transformation Masks', 'rules' => [
                ['rule' => 'No Transformation Masks', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Transform', 'card' => 'ace_of_wands', 'pos' => 2, 'default' => false],
                ['rule' => 'Use Bottle', 'card' => 'ace_of_cups', 'pos' => 3, 'default' => false],
            ]],

            // Zelda: Twilight Princess
            ['name' => 'Zelda TP: 3 Heart Challenge', 'rules' => [
                ['rule' => 'No Extra Hearts', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Collect Heart', 'card' => 'ace_of_cups', 'pos' => 2, 'default' => false],
                ['rule' => 'Use Bottle', 'card' => 'ace_of_wands', 'pos' => 3, 'default' => false],
            ]],
            ['name' => 'Zelda TP: No Bottle/Fairy', 'rules' => [
                ['rule' => 'No Bottles', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'No Fairy Revival', 'card' => 'the_magician', 'pos' => 2, 'default' => true],
                ['rule' => 'Use Bottle', 'card' => 'ace_of_cups', 'pos' => 3, 'default' => false],
            ]],
            ['name' => 'Zelda TP: No Wolf Form', 'rules' => [
                ['rule' => 'No Wolf Form', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Transform', 'card' => 'ace_of_wands', 'pos' => 2, 'default' => false],
            ]],

            // Zelda: The Wind Waker
            ['name' => 'Zelda TWW: 3 Heart Challenge', 'rules' => [
                ['rule' => 'No Extra Hearts', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Collect Heart', 'card' => 'ace_of_cups', 'pos' => 2, 'default' => false],
            ]],
            ['name' => 'Zelda TWW: No Bottle/Fairy', 'rules' => [
                ['rule' => 'No Bottles', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'No Fairy Revival', 'card' => 'the_magician', 'pos' => 2, 'default' => true],
                ['rule' => 'Use Bottle', 'card' => 'ace_of_cups', 'pos' => 3, 'default' => false],
            ]],
            ['name' => 'Zelda TWW: No Tingle Tuner', 'rules' => [
                ['rule' => 'No Tingle Tuner', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
            ]],

            // Zelda: A Link to the Past
            ['name' => 'Zelda ALTTP: 3 Heart Challenge', 'rules' => [
                ['rule' => 'No Extra Hearts', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Collect Heart', 'card' => 'ace_of_cups', 'pos' => 2, 'default' => false],
            ]],
            ['name' => 'Zelda ALTTP: No Armor Upgrades', 'rules' => [
                ['rule' => 'No Armor Upgrades', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Upgrade Armor', 'card' => 'ace_of_pentacles', 'pos' => 2, 'default' => false],
            ]],
            ['name' => 'Zelda ALTTP: No Bottle', 'rules' => [
                ['rule' => 'No Bottles', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Use Bottle', 'card' => 'ace_of_cups', 'pos' => 2, 'default' => false],
            ]],

            // Zelda: Link's Awakening
            ['name' => 'Zelda LA: 3 Heart Challenge', 'rules' => [
                ['rule' => 'No Extra Hearts', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Collect Heart', 'card' => 'ace_of_cups', 'pos' => 2, 'default' => false],
            ]],
            ['name' => 'Zelda LA: No Guardian Acorns/Pieces', 'rules' => [
                ['rule' => 'No Guardian Acorns', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
            ]],
            ['name' => 'Zelda LA: Swordless', 'rules' => [
                ['rule' => 'No Sword', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Use Sword', 'card' => 'ace_of_swords', 'pos' => 2, 'default' => false],
            ]],

            // Zelda: Breath of the Wild
            ['name' => 'Zelda BOTW: 3 Heart Challenge', 'rules' => [
                ['rule' => 'No Extra Hearts', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Collect Heart', 'card' => 'ace_of_cups', 'pos' => 2, 'default' => false],
            ]],
            ['name' => 'Zelda BOTW: No Healing Food', 'rules' => [
                ['rule' => 'No Healing Food', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
            ]],
            ['name' => 'Zelda BOTW: No Sheikah Slate', 'rules' => [
                ['rule' => 'No Sheikah Slate', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
            ]],

            // Zelda: Tears of the Kingdom
            ['name' => 'Zelda TOTK: 3 Heart Challenge', 'rules' => [
                ['rule' => 'No Extra Hearts', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Collect Heart', 'card' => 'ace_of_cups', 'pos' => 2, 'default' => false],
            ]],
            ['name' => 'Zelda TOTK: No Fuse Ability', 'rules' => [
                ['rule' => 'No Fuse', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
            ]],
            ['name' => 'Zelda TOTK: No Healing Food', 'rules' => [
                ['rule' => 'No Healing Food', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
            ]],

            // Zelda: Skyward Sword
            ['name' => 'Zelda SS: 3 Heart Challenge', 'rules' => [
                ['rule' => 'No Extra Hearts', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Collect Heart', 'card' => 'ace_of_cups', 'pos' => 2, 'default' => false],
            ]],
            ['name' => 'Zelda SS: No Bottle/Potion', 'rules' => [
                ['rule' => 'No Bottles', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'No Potions', 'card' => 'the_magician', 'pos' => 2, 'default' => true],
                ['rule' => 'Use Bottle', 'card' => 'ace_of_cups', 'pos' => 3, 'default' => false],
            ]],

            // Hades
            ['name' => 'Hades: No Boons Challenge', 'rules' => [
                ['rule' => 'No Boons', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Accept Boon', 'card' => 'ace_of_pentacles', 'pos' => 2, 'default' => false],
            ]],
            ['name' => 'Hades: Fresh File', 'rules' => [
                ['rule' => 'No Mirror Upgrades', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Use Mirror', 'card' => 'ace_of_pentacles', 'pos' => 2, 'default' => false],
            ]],

            // Hades II
            ['name' => 'Hades II: No Boons Challenge', 'rules' => [
                ['rule' => 'No Boons', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Accept Boon', 'card' => 'ace_of_pentacles', 'pos' => 2, 'default' => false],
            ]],
            ['name' => 'Hades II: No Arcana Cards', 'rules' => [
                ['rule' => 'No Arcana Cards', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
            ]],

            // Binding of Isaac
            ['name' => 'Isaac: Base Damage Only', 'rules' => [
                ['rule' => 'No Damage Items', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Pick Damage Item', 'card' => 'ace_of_pentacles', 'pos' => 2, 'default' => false],
            ]],
            ['name' => 'Isaac: Blindfolded Run', 'rules' => [
                ['rule' => 'No Tears', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Fire Tears', 'card' => 'ace_of_wands', 'pos' => 2, 'default' => false],
            ]],
            ['name' => 'Isaac: No Soul/Black Hearts', 'rules' => [
                ['rule' => 'No Soul Hearts', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Pick Soul Heart', 'card' => 'ace_of_cups', 'pos' => 2, 'default' => false],
            ]],

            // Binding of Isaac: Rebirth
            ['name' => 'Isaac Rebirth: Base Damage Only', 'rules' => [
                ['rule' => 'No Damage Items', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Pick Damage Item', 'card' => 'ace_of_pentacles', 'pos' => 2, 'default' => false],
            ]],
            ['name' => 'Isaac Rebirth: Blindfolded Run', 'rules' => [
                ['rule' => 'No Tears', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Fire Tears', 'card' => 'ace_of_wands', 'pos' => 2, 'default' => false],
            ]],
            ['name' => 'Isaac Rebirth: No Soul/Black Hearts', 'rules' => [
                ['rule' => 'No Soul Hearts', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Pick Soul Heart', 'card' => 'ace_of_cups', 'pos' => 2, 'default' => false],
            ]],

            // Slay the Spire
            ['name' => 'Slay the Spire: Starter Deck Only', 'rules' => [
                ['rule' => 'No Card Additions', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Add Card', 'card' => 'ace_of_pentacles', 'pos' => 2, 'default' => false],
            ]],
            ['name' => 'Slay the Spire: No Rare Cards', 'rules' => [
                ['rule' => 'No Rare Cards', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Pick Rare Card', 'card' => 'ace_of_pentacles', 'pos' => 2, 'default' => false],
            ]],
            ['name' => 'Slay the Spire: Minimalist Run', 'rules' => [
                ['rule' => 'Minimize Deck', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Skip Remove', 'card' => 'ace_of_pentacles', 'pos' => 2, 'default' => false],
            ]],

            // Balatro
            ['name' => 'Balatro: Base Deck Only', 'rules' => [
                ['rule' => 'No Jokers', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Add Joker', 'card' => 'ace_of_pentacles', 'pos' => 2, 'default' => false],
            ]],
            ['name' => 'Balatro: One Hand Type', 'rules' => [
                ['rule' => 'One Hand Type', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Score Different Hand', 'card' => 'ace_of_pentacles', 'pos' => 2, 'default' => false],
            ]],

            // The Witness
            ['name' => 'The Witness: No Guide Challenge', 'rules' => [
                ['rule' => 'No Guides', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Look Up Solution', 'card' => 'ace_of_pentacles', 'pos' => 2, 'default' => false],
            ]],
            ['name' => 'The Witness: Random Start', 'rules' => [
                ['rule' => 'Random Order', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
            ]],
            ['name' => 'The Witness: Speed Challenge', 'rules' => [
                ['rule' => 'Speedrun Mode', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
            ]],

            // Category rulesets
            ['name' => 'Metroidvania: Linear Speedrun', 'rules' => [
                ['rule' => 'No Backtracking', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Backtrack', 'card' => 'ace_of_pentacles', 'pos' => 2, 'default' => false],
            ]],
            ['name' => 'Roguelike: Pure RNG Challenge', 'rules' => [
                ['rule' => 'No Rerolls', 'card' => 'the_fool', 'pos' => 1, 'default' => true],
                ['rule' => 'Reroll', 'card' => 'ace_of_pentacles', 'pos' => 2, 'default' => false],
            ]],
        ];

        foreach ($rulesetConfigs as $config) {
            $ruleset = $manager->getRepository(Ruleset::class)->findOneBy(['name' => $config['name']]);
            if (!$ruleset) {
                echo "Ruleset not found: {$config['name']}\n";
                continue;
            }

            foreach ($config['rules'] as $ruleData) {
                $rule = $manager->getRepository(Rule::class)->findOneBy(['name' => $ruleData['rule']]);
                if (!$rule) {
                    echo "Rule not found: {$ruleData['rule']}\n";
                    continue;
                }

                $tarotCard = $manager->getRepository(TarotCard::class)->findOneBy(['identifier' => $ruleData['card']]);
                if (!$tarotCard) {
                    echo "Tarot card not found: {$ruleData['card']}\n";
                    continue;
                }

                $ruleCard = new RulesetRuleCard();
                $ruleCard->setRuleset($ruleset);
                $ruleCard->setRule($rule);
                $ruleCard->setTarotCard($tarotCard);
                $ruleCard->setPosition($ruleData['pos']);
                $ruleCard->setIsDefault($ruleData['default']);

                $manager->persist($ruleCard);
            }
        }
    }

    public function getDependencies(): array
    {
        return [
            TarotCardFixtures::class,
            CategoryFixtures::class,
            GameFixtures::class,
            RuleFixtures::class,
            PopularRulesetsFixtures::class, // Run after existing rulesets
        ];
    }
}
