<?php

namespace App\DataFixtures;

use App\Entity\CardDesign;
use App\Entity\DesignName;
use App\Entity\DesignSet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DesignFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // ===== DEFAULT FREE SETS (Always Available) =====

        // 1. Text Only (Free - Default)
        $textOnlyName = new DesignName();
        $textOnlyName->setName('Text Only');
        $textOnlyName->setDescription('Simple text-based display without card artwork');
        $manager->persist($textOnlyName);

        $textOnlySet = new DesignSet();
        $textOnlySet->setDesignName($textOnlyName);
        $textOnlySet->setType('template'); // Text-only is a template type
        $textOnlySet->setIsFree(true);
        $textOnlySet->setIsPremium(false);
        $textOnlySet->setTheme('minimal');
        $textOnlySet->setDescription('Clean text display without any card artwork. Perfect for performance and accessibility. Free for all users.');
        $manager->persist($textOnlySet);

        // Text Only uses basic templates (no actual images needed)
        $this->createTemplateCards($manager, $textOnlySet);

        // 2. Icon Only (Free - Default)
        $iconOnlyName = new DesignName();
        $iconOnlyName->setName('Icon Only');
        $iconOnlyName->setDescription('Icon-based display with rule icons overlaid on simple frames');
        $manager->persist($iconOnlyName);

        $iconOnlySet = new DesignSet();
        $iconOnlySet->setDesignName($iconOnlyName);
        $iconOnlySet->setType('template');
        $iconOnlySet->setIsFree(true);
        $iconOnlySet->setIsPremium(false);
        $iconOnlySet->setTheme('icon');
        $iconOnlySet->setDescription('Simple frames with rule icons dynamically overlaid. Lightweight and functional. Free for all users.');
        $manager->persist($iconOnlySet);

        $this->createTemplateCards($manager, $iconOnlySet);

        // 3. Icon + Text (Free - Default)
        $iconTextName = new DesignName();
        $iconTextName->setName('Icon + Text');
        $iconTextName->setDescription('Icon with text label below for clear identification');
        $manager->persist($iconTextName);

        $iconTextSet = new DesignSet();
        $iconTextSet->setDesignName($iconTextName);
        $iconTextSet->setType('template');
        $iconTextSet->setIsFree(true);
        $iconTextSet->setIsPremium(false);
        $iconTextSet->setTheme('icon-text');
        $iconTextSet->setDescription('Rule icons with text labels below for maximum clarity. Perfect balance of visual and textual information. Free for all users.');
        $manager->persist($iconTextSet);

        $this->createTemplateCards($manager, $iconTextSet);

        $manager->flush();

        echo "\n✓ Created 3 default design sets:\n";
        echo "  - Text Only (template) - Clean text display, no artwork\n";
        echo "  - Icon Only (template) - Simple frames with rule icons\n";
        echo "  - Icon + Text (template) - Icons with text labels\n";
        echo "  Total card designs: 9 (3 templates each)\n";
        echo "\n  Note: Additional design sets should be added via admin panel.\n";
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
