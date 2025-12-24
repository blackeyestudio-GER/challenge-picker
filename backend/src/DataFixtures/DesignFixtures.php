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
        // Create Gothic Design Name
        $gothicDesignName = new DesignName();
        $gothicDesignName->setName('Gothic');
        $gothicDesignName->setDescription('Dark and mysterious gothic-themed tarot card designs');
        $manager->persist($gothicDesignName);

        // Create Gothic Design Set
        $gothicDesignSet = new DesignSet();
        $gothicDesignSet->setDesignName($gothicDesignName);
        $manager->persist($gothicDesignSet);

        // Create all 78 card designs for Gothic set (without images for now)
        $allCards = TarotCardIdentifier::cases();

        foreach ($allCards as $cardIdentifier) {
            $cardDesign = new CardDesign();
            $cardDesign->setDesignSet($gothicDesignSet);
            $cardDesign->setCardIdentifier($cardIdentifier->value);
            // imageBase64 is null for now - will be added later
            $cardDesign->setImageBase64(null);

            $manager->persist($cardDesign);
        }

        $manager->flush();

        echo sprintf(
            "\n✓ Created Gothic design set with %d card designs (images not yet added)\n",
            count($allCards)
        );
    }
}
