<?php

namespace App\DataFixtures;

use App\Entity\CardDesign;
use App\Entity\DesignName;
use App\Entity\DesignSet;
use App\Enum\TarotCardIdentifier;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DesignFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // ===== FREE SETS (3 for user choice) =====

        // 1. Gothic Full Set (Free)
        $gothicName = new DesignName();
        $gothicName->setName('Gothic');
        $gothicName->setDescription('Dark and mysterious gothic-themed tarot card designs');
        $manager->persist($gothicName);

        $gothicSet = new DesignSet();
        $gothicSet->setDesignName($gothicName);
        $gothicSet->setType('full');
        $gothicSet->setIsPremium(false);
        $gothicSet->setTheme('gothic');
        $gothicSet->setDescription('Classic gothic full artwork for all 78 tarot cards. Free for all users.');
        $gothicSet->setSortOrder(1);
        $manager->persist($gothicSet);

        // Create all 78 card designs
        $allCards = TarotCardIdentifier::cases();
        foreach ($allCards as $cardIdentifier) {
            $cardDesign = new CardDesign();
            $cardDesign->setDesignSet($gothicSet);
            $cardDesign->setCardIdentifier($cardIdentifier->value);
            $cardDesign->setIsTemplate(false);
            $cardDesign->setImageBase64(null); // Will be uploaded via admin
            $manager->persist($cardDesign);
        }

        // 2. Minimalist Template (Free)
        $minimalistName = new DesignName();
        $minimalistName->setName('Minimalist');
        $minimalistName->setDescription('Clean, modern template designs with icon composition');
        $manager->persist($minimalistName);

        $minimalistSet = new DesignSet();
        $minimalistSet->setDesignName($minimalistName);
        $minimalistSet->setType('template');
        $minimalistSet->setIsPremium(false);
        $minimalistSet->setTheme('modern');
        $minimalistSet->setDescription('Simple and elegant template frames. Icons overlay dynamically based on rules. Free for all users.');
        $minimalistSet->setSortOrder(2);
        $manager->persist($minimalistSet);

        // Create 3 templates
        $this->createTemplateCards($manager, $minimalistSet);

        // 3. Pixel Art Full Set (Free)
        $pixelName = new DesignName();
        $pixelName->setName('Pixel Art');
        $pixelName->setDescription('Retro gaming pixel art style for all cards');
        $manager->persist($pixelName);

        $pixelSet = new DesignSet();
        $pixelSet->setDesignName($pixelName);
        $pixelSet->setType('full');
        $pixelSet->setIsPremium(false);
        $pixelSet->setTheme('retro');
        $pixelSet->setDescription('8-bit retro gaming style full artwork. Free for all users.');
        $pixelSet->setSortOrder(3);
        $manager->persist($pixelSet);

        foreach ($allCards as $cardIdentifier) {
            $cardDesign = new CardDesign();
            $cardDesign->setDesignSet($pixelSet);
            $cardDesign->setCardIdentifier($cardIdentifier->value);
            $cardDesign->setIsTemplate(false);
            $cardDesign->setImageBase64(null);
            $manager->persist($cardDesign);
        }

        // ===== PREMIUM SETS (Examples) =====

        // 4. Horror Template (Premium)
        $horrorName = new DesignName();
        $horrorName->setName('Horror');
        $horrorName->setDescription('Terrifying horror-themed template designs');
        $manager->persist($horrorName);

        $horrorTemplateSet = new DesignSet();
        $horrorTemplateSet->setDesignName($horrorName);
        $horrorTemplateSet->setType('template');
        $horrorTemplateSet->setIsPremium(true);
        $horrorTemplateSet->setPrice('2.99');
        $horrorTemplateSet->setTheme('horror');
        $horrorTemplateSet->setDescription('Blood-soaked frames with horror aesthetics. Perfect for horror game challenges.');
        $horrorTemplateSet->setSortOrder(10);
        $manager->persist($horrorTemplateSet);

        $this->createTemplateCards($manager, $horrorTemplateSet);

        // 5. Cyberpunk Template (Premium)
        $cyberpunkName = new DesignName();
        $cyberpunkName->setName('Cyberpunk');
        $cyberpunkName->setDescription('Futuristic cyberpunk neon template designs');
        $manager->persist($cyberpunkName);

        $cyberpunkTemplateSet = new DesignSet();
        $cyberpunkTemplateSet->setDesignName($cyberpunkName);
        $cyberpunkTemplateSet->setType('template');
        $cyberpunkTemplateSet->setIsPremium(true);
        $cyberpunkTemplateSet->setPrice('2.99');
        $cyberpunkTemplateSet->setTheme('cyberpunk');
        $cyberpunkTemplateSet->setDescription('Neon-lit futuristic frames for sci-fi gaming. Icons glow with cyberpunk aesthetics.');
        $cyberpunkTemplateSet->setSortOrder(11);
        $manager->persist($cyberpunkTemplateSet);

        $this->createTemplateCards($manager, $cyberpunkTemplateSet);

        $manager->flush();

        echo "\n✓ Created 5 design sets:\n";
        echo "  - 3 FREE: Gothic (full), Minimalist (template), Pixel Art (full)\n";
        echo "  - 2 PREMIUM: Horror Template (\$2.99), Cyberpunk Template (\$2.99)\n";
        echo '  Total card designs: ' . (78 + 3 + 78 + 3 + 3) . " (165 placeholders)\n";
    }

    private function createTemplateCards(ObjectManager $manager, DesignSet $designSet): void
    {
        $templates = [
            ['identifier' => 'TEMPLATE_BASIC', 'type' => 'basic', 'name' => 'Basic Template'],
            ['identifier' => 'TEMPLATE_COURT', 'type' => 'court', 'name' => 'Court Template'],
            ['identifier' => 'TEMPLATE_LEGENDARY', 'type' => 'legendary', 'name' => 'Legendary Template'],
        ];

        foreach ($templates as $template) {
            $cardDesign = new CardDesign();
            $cardDesign->setDesignSet($designSet);
            $cardDesign->setCardIdentifier($template['identifier']);
            $cardDesign->setIsTemplate(true);
            $cardDesign->setTemplateType($template['type']);
            $cardDesign->setImageBase64(null); // Will be uploaded via admin
            $manager->persist($cardDesign);
        }
    }
}
