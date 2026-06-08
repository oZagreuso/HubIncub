<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260528113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normaliser les accents des statuts des portfolios de test';
    }

    public function up(Schema $schema): void
    {
        // Normalise les données de test locales créées depuis des terminaux ayant remplacé les caractères accentués.
        $this->addSql("UPDATE portfolio SET role = 'Ancien étudiant' WHERE role = 'Ancien ?tudiant'");
    }

    public function down(Schema $schema): void
    {
    }
}
