<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251222202044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tarot_cards reference table and seed with all 78 cards in ARPG order';
    }

    public function up(Schema $schema): void
    {
        // Create the tarot_cards table
        $this->addSql('CREATE TABLE tarot_cards (identifier VARCHAR(50) NOT NULL, display_name VARCHAR(100) NOT NULL, rarity VARCHAR(20) NOT NULL, suit VARCHAR(20) DEFAULT NULL, card_value SMALLINT NOT NULL, sort_order SMALLINT NOT NULL, PRIMARY KEY (identifier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        
        $sortOrder = 0;
        $suits = ['Wands', 'Cups', 'Swords', 'Pentacles'];
        
        // 1. LEGENDARY: Major Arcana (0-21) - 22 cards
        $majorArcana = [
            ['The_Fool', 'The Fool', 0],
            ['The_Magician', 'The Magician', 1],
            ['The_High_Priestess', 'The High Priestess', 2],
            ['The_Empress', 'The Empress', 3],
            ['The_Emperor', 'The Emperor', 4],
            ['The_Hierophant', 'The Hierophant', 5],
            ['The_Lovers', 'The Lovers', 6],
            ['The_Chariot', 'The Chariot', 7],
            ['Strength', 'Strength', 8],
            ['The_Hermit', 'The Hermit', 9],
            ['Wheel_Of_Fortune', 'Wheel Of Fortune', 10],
            ['Justice', 'Justice', 11],
            ['The_Hanged_Man', 'The Hanged Man', 12],
            ['Death', 'Death', 13],
            ['Temperance', 'Temperance', 14],
            ['The_Devil', 'The Devil', 15],
            ['The_Tower', 'The Tower', 16],
            ['The_Star', 'The Star', 17],
            ['The_Moon', 'The Moon', 18],
            ['The_Sun', 'The Sun', 19],
            ['Judgement', 'Judgement', 20],
            ['The_World', 'The World', 21],
        ];
        
        foreach ($majorArcana as [$identifier, $displayName, $value]) {
            $this->addSql(
                "INSERT INTO tarot_cards (identifier, display_name, rarity, suit, card_value, sort_order) VALUES (?, ?, 'legendary', NULL, ?, ?)",
                [$identifier, $displayName, $value, $sortOrder++]
            );
        }
        
        // 2. RARE: Court cards (22-37) - 16 cards
        $courtCards = [
            ['Page', 'Page', 11],
            ['Knight', 'Knight', 12],
            ['Queen', 'Queen', 13],
            ['King', 'King', 14],
        ];
        
        foreach ($suits as $suit) {
            foreach ($courtCards as [$suffix, $displaySuffix, $value]) {
                $identifier = "{$suit}_{$suffix}";
                $displayName = "{$displaySuffix} of {$suit}";
                $this->addSql(
                    "INSERT INTO tarot_cards (identifier, display_name, rarity, suit, card_value, sort_order) VALUES (?, ?, 'rare', ?, ?, ?)",
                    [$identifier, $displayName, $suit, $value, $sortOrder++]
                );
            }
        }
        
        // 3. UNCOMMON: Seven through Ten (38-53) - 16 cards
        $uncommonCards = [
            ['Seven', 'Seven', 7],
            ['Eight', 'Eight', 8],
            ['Nine', 'Nine', 9],
            ['Ten', 'Ten', 10],
        ];
        
        foreach ($suits as $suit) {
            foreach ($uncommonCards as [$suffix, $displaySuffix, $value]) {
                $identifier = "{$suit}_{$suffix}";
                $displayName = "{$displaySuffix} of {$suit}";
                $this->addSql(
                    "INSERT INTO tarot_cards (identifier, display_name, rarity, suit, card_value, sort_order) VALUES (?, ?, 'uncommon', ?, ?, ?)",
                    [$identifier, $displayName, $suit, $value, $sortOrder++]
                );
            }
        }
        
        // 4. COMMON: Ace through Six (54-77) - 24 cards
        $commonCards = [
            ['Ace', 'Ace', 1],
            ['Two', 'Two', 2],
            ['Three', 'Three', 3],
            ['Four', 'Four', 4],
            ['Five', 'Five', 5],
            ['Six', 'Six', 6],
        ];
        
        foreach ($suits as $suit) {
            foreach ($commonCards as [$suffix, $displaySuffix, $value]) {
                $identifier = "{$suit}_{$suffix}";
                $displayName = "{$displaySuffix} of {$suit}";
                $this->addSql(
                    "INSERT INTO tarot_cards (identifier, display_name, rarity, suit, card_value, sort_order) VALUES (?, ?, 'common', ?, ?, ?)",
                    [$identifier, $displayName, $suit, $value, $sortOrder++]
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE tarot_cards');
    }
}
