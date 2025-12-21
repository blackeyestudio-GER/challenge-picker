<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221215946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_category_representative flag to games and mark category representative games';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE games ADD is_category_representative TINYINT(1) DEFAULT 0 NOT NULL');
        
        // Mark the 7 category representative games
        $this->addSql("UPDATE games SET is_category_representative = 1 WHERE name IN ('FPS', 'Soulslikes', '3rd Person Shooter', 'Horror', 'Zombies', 'Aliens', 'Monsters')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE games DROP is_category_representative');
    }
}
