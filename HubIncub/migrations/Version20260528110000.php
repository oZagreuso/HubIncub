<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260528110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajouter cinq portfolios de test pour les anciens';
    }

    public function up(Schema $schema): void
    {
        // Les lignes de test sont insérées uniquement lorsque leur email unique n'existe pas déjà.
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Camille', 'Martin', 'Ancien étudiant', 'https://example.com/camille-martin', 'camille.martin@example.com', 'https://www.linkedin.com/in/camille-martin-test' WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'camille.martin@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Yanis', 'Bernard', 'Ancien étudiant', 'https://example.com/yanis-bernard', 'yanis.bernard@example.com', NULL WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'yanis.bernard@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Lea', 'Petit', 'Ancien étudiant', 'https://example.com/lea-petit', 'lea.petit@example.com', 'https://www.linkedin.com/in/lea-petit-test' WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'lea.petit@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Noah', 'Morel-Dupont', 'Ancien étudiant', 'https://example.com/noah-morel-dupont', 'noah.morel-dupont@example.com', NULL WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'noah.morel-dupont@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Ines', 'Roche', 'Ancien étudiant', 'https://example.com/ines-roche', 'ines.roche.long-email-test@example.com', 'https://www.linkedin.com/in/ines-roche-test' WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'ines.roche.long-email-test@example.com')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM portfolio WHERE email IN ('camille.martin@example.com', 'yanis.bernard@example.com', 'lea.petit@example.com', 'noah.morel-dupont@example.com', 'ines.roche.long-email-test@example.com')");
    }
}
