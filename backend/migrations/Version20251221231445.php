<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221231445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add vote_type to game_category_votes to support upvotes and downvotes';
    }

    public function up(Schema $schema): void
    {
        // Add vote_type column with default value of 1 (upvote) for existing votes
        $this->addSql('ALTER TABLE game_category_votes ADD vote_type SMALLINT NOT NULL DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE game_category_votes DROP vote_type');
    }
}
