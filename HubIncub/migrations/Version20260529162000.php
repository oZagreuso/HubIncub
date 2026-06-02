<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260529162000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add promotion to portfolio members';
    }

    public function up(Schema $schema): void
    {
        $columns = $this->connection->fetchFirstColumn("SELECT name FROM pragma_table_info('portfolio')");

        if (!in_array('promotion', $columns, true)) {
            $this->addSql('ALTER TABLE portfolio ADD COLUMN promotion VARCHAR(20) DEFAULT NULL');
        }

        $this->addSql("UPDATE portfolio SET promotion = '2026' WHERE lower(first_name) = 'olivier' AND lower(last_name) = 'dal ferro'");
        $this->addSql("UPDATE portfolio SET promotion = '2025' WHERE email IN ('aline.chevalier@example.com', 'bastien.lemoine@example.com', 'camille.martin@example.com', 'chloe.garnier@example.com', 'dorian.masson@example.com', 'elisa.renard@example.com')");
        $this->addSql("UPDATE portfolio SET promotion = '2024' WHERE email IN ('adel.bourgeois@example.com', 'farid.colin@example.com', 'gaelle.marchand@example.com', 'hugo.perrin@example.com', 'imane.roussel@example.com', 'jules.fontaine@example.com', 'karima.blanc@example.com')");
        $this->addSql("UPDATE portfolio SET promotion = '2023' WHERE email IN ('ines.roche.long-email-test@example.com', 'lea.petit@example.com', 'loris.meunier@example.com', 'maeva.picard@example.com', 'nassim.guillot@example.com', 'noah.morel-dupont@example.com')");
        $this->addSql("UPDATE portfolio SET promotion = '2022' WHERE email IN ('oceane.lambert@example.com', 'paul.aubert@example.com', 'rania.delmas@example.com', 'sacha.vidal@example.com', 'tania.leroy-simon-long-test@example.com', 'yanis.bernard@example.com')");
    }

    public function down(Schema $schema): void
    {
        $columns = $this->connection->fetchFirstColumn("SELECT name FROM pragma_table_info('portfolio')");

        if (in_array('promotion', $columns, true)) {
            $this->addSql('ALTER TABLE portfolio DROP COLUMN promotion');
        }
    }
}
