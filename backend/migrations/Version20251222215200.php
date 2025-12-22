<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Clean up duplicate games, keeping the ones with store links
 */
final class Version20251222215200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove duplicate games, keeping entries with store links';
    }

    public function up(Schema $schema): void
    {
        $duplicateGames = ['Counter-Strike 2', 'DOOM Eternal', 'Elden Ring', 'Resident Evil 4'];
        
        foreach ($duplicateGames as $gameName) {
            // Get IDs ordered by having links (with links first)
            $this->addSql(
                "SELECT id FROM games WHERE name = ? ORDER BY 
                 (steam_link IS NOT NULL OR epic_link IS NOT NULL OR gog_link IS NOT NULL) DESC, 
                 id ASC",
                [$gameName]
            );
            
            // Keep the first one (with links), delete others
            $this->addSql(
                "DELETE FROM games 
                 WHERE name = ? 
                 AND id NOT IN (
                     SELECT * FROM (
                         SELECT id FROM games WHERE name = ? 
                         ORDER BY (steam_link IS NOT NULL OR epic_link IS NOT NULL OR gog_link IS NOT NULL) DESC, id ASC 
                         LIMIT 1
                     ) AS keeper
                 )",
                [$gameName, $gameName]
            );
        }
    }

    public function down(Schema $schema): void
    {
        // Cannot restore deleted duplicates
    }
}

