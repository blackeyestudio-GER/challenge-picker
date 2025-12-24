<?php

namespace App\DataFixtures;

use App\Entity\TarotCard;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TarotCardFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $cards = $this->getCardsData();

        foreach ($cards as $data) {
            $card = new TarotCard();
            $card->setIdentifier($data['identifier']);
            $card->setDisplayName($data['display_name']);
            $card->setRarity($data['rarity']);
            $card->setSuit($data['suit'] ?? null);
            $card->setCardValue($data['card_value']);
            $card->setSortOrder($data['sort_order']);

            $manager->persist($card);

            // Add reference for later use
            $this->addReference('tarot_card_' . $data['identifier'], $card);
        }

        $manager->flush();
    }

    private function getCardsData(): array
    {
        $cards = [];
        $sortOrder = 0;

        // Major Arcana (Legendary - 22 cards)
        $majorArcana = [
            'The Fool', 'The Magician', 'The High Priestess', 'The Empress', 'The Emperor',
            'The Hierophant', 'The Lovers', 'The Chariot', 'Strength', 'The Hermit',
            'Wheel of Fortune', 'Justice', 'The Hanged Man', 'Death', 'Temperance',
            'The Devil', 'The Tower', 'The Star', 'The Moon', 'The Sun',
            'Judgement', 'The World',
        ];

        foreach ($majorArcana as $index => $name) {
            $cards[] = [
                'identifier' => strtolower(str_replace(' ', '_', $name)),
                'display_name' => $name,
                'rarity' => 'legendary',
                'suit' => null,
                'card_value' => $index,
                'sort_order' => $sortOrder++,
            ];
        }

        // Minor Arcana
        $suits = ['Wands', 'Cups', 'Swords', 'Pentacles'];
        foreach ($suits as $suit) {
            // Ace (Uncommon)
            $cards[] = [
                'identifier' => 'ace_of_' . strtolower($suit),
                'display_name' => 'Ace of ' . $suit,
                'rarity' => 'uncommon',
                'suit' => $suit,
                'card_value' => 1,
                'sort_order' => $sortOrder++,
            ];

            // Number cards 2-10 (Common)
            for ($i = 2; $i <= 10; ++$i) {
                $cards[] = [
                    'identifier' => $i . '_of_' . strtolower($suit),
                    'display_name' => $i . ' of ' . $suit,
                    'rarity' => 'common',
                    'suit' => $suit,
                    'card_value' => $i,
                    'sort_order' => $sortOrder++,
                ];
            }

            // Court Cards (Rare)
            $courtCards = ['Page' => 11, 'Knight' => 12, 'Queen' => 13, 'King' => 14];
            foreach ($courtCards as $court => $value) {
                $cards[] = [
                    'identifier' => strtolower($court) . '_of_' . strtolower($suit),
                    'display_name' => $court . ' of ' . $suit,
                    'rarity' => 'rare',
                    'suit' => $suit,
                    'card_value' => $value,
                    'sort_order' => $sortOrder++,
                ];
            }
        }

        return $cards;
    }
}
