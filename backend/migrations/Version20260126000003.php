<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add designer support to DesignSet and create DesignerEarnings table.
 * (Renamed from Version20250107000000 — that timestamp sorted before the initial schema migration.)
 */
final class Version20260126000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add designer support: designer relationship and fee to design_sets, create designer_earnings table';
    }

    public function up(Schema $schema): void
    {
        // Add designer and designerFee to design_sets table
        $this->addSql('ALTER TABLE design_sets ADD designer_uuid BINARY(16) DEFAULT NULL, ADD designer_fee NUMERIC(5, 4) DEFAULT NULL');
        $this->addSql('ALTER TABLE design_sets ADD CONSTRAINT FK_DESIGN_SETS_DESIGNER FOREIGN KEY (designer_uuid) REFERENCES users (uuid) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_DESIGN_SETS_DESIGNER ON design_sets (designer_uuid)');

        // Create designer_earnings table
        $this->addSql('CREATE TABLE designer_earnings (
            id INT AUTO_INCREMENT NOT NULL,
            designer_uuid BINARY(16) NOT NULL,
            design_set_id INT NOT NULL,
            purchase_id INT NOT NULL,
            amount NUMERIC(10, 2) NOT NULL,
            fee_percentage NUMERIC(5, 4) NOT NULL,
            purchase_price NUMERIC(10, 2) NOT NULL,
            currency VARCHAR(3) DEFAULT NULL,
            earned_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_DESIGNER_EARNINGS_DESIGNER (designer_uuid),
            INDEX IDX_DESIGNER_EARNINGS_DESIGN_SET (design_set_id),
            INDEX IDX_DESIGNER_EARNINGS_PURCHASE (purchase_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE designer_earnings ADD CONSTRAINT FK_DESIGNER_EARNINGS_DESIGNER FOREIGN KEY (designer_uuid) REFERENCES users (uuid) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE designer_earnings ADD CONSTRAINT FK_DESIGNER_EARNINGS_DESIGN_SET FOREIGN KEY (design_set_id) REFERENCES design_sets (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE designer_earnings ADD CONSTRAINT FK_DESIGNER_EARNINGS_PURCHASE FOREIGN KEY (purchase_id) REFERENCES user_design_sets (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Drop designer_earnings table
        $this->addSql('DROP TABLE designer_earnings');

        // Remove designer fields from design_sets
        $this->addSql('ALTER TABLE design_sets DROP FOREIGN KEY FK_DESIGN_SETS_DESIGNER');
        $this->addSql('DROP INDEX IDX_DESIGN_SETS_DESIGNER ON design_sets');
        $this->addSql('ALTER TABLE design_sets DROP designer_uuid, DROP designer_fee');
    }
}
