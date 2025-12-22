<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Restore game images from backup
 */
final class Version20251222220255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Restore 87 game images from JSON backup (captured on 2025-12-22)';
    }

    public function up(Schema $schema): void
    {
        // Load game images from backup file
        $backupFile = __DIR__ . '/../game_images_backup.json';
        
        if (!file_exists($backupFile)) {
            $this->write('Warning: game_images_backup.json not found. Skipping image restoration.');
            return;
        }

        $games = json_decode(file_get_contents($backupFile), true);
        
        if (!$games) {
            $this->write('Warning: Could not parse game_images_backup.json. Skipping image restoration.');
            return;
        }

        $this->write(sprintf('Restoring images for %d games...', count($games)));

        foreach ($games as $game) {
            $id = (int)$game['id'];
            $image = $this->connection->quote($game['image']);
            
            $this->addSql("UPDATE games SET image = {$image} WHERE id = {$id}");
        }

        $this->write(sprintf('Successfully restored images for %d games', count($games)));
    }

    public function down(Schema $schema): void
    {
        // Optionally clear all images on down migration
        // $this->addSql('UPDATE games SET image = NULL');
        
        $this->write('Note: Images were not removed. Run app:fetch-game-icons to re-download if needed.');
    }
}
