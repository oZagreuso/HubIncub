<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260528111000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add twenty extra sample alumni portfolios';
    }

    public function up(Schema $schema): void
    {
        // Additional sample rows expand the portfolio grid for layout validation.
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Adel', 'Bourgeois', 'Ancien étudiant', 'https://example.com/adel-bourgeois', 'adel.bourgeois@example.com', NULL WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'adel.bourgeois@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Aline', 'Chevalier', 'Ancien étudiant', 'https://example.com/aline-chevalier', 'aline.chevalier@example.com', 'https://www.linkedin.com/in/aline-chevalier-test' WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'aline.chevalier@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Bastien', 'Lemoine', 'Ancien étudiant', 'https://example.com/bastien-lemoine', 'bastien.lemoine@example.com', NULL WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'bastien.lemoine@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Chloe', 'Garnier', 'Ancien étudiant', 'https://example.com/chloe-garnier', 'chloe.garnier@example.com', 'https://www.linkedin.com/in/chloe-garnier-test' WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'chloe.garnier@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Dorian', 'Masson', 'Ancien étudiant', 'https://example.com/dorian-masson', 'dorian.masson@example.com', NULL WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'dorian.masson@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Elisa', 'Renard', 'Ancien étudiant', 'https://example.com/elisa-renard', 'elisa.renard@example.com', 'https://www.linkedin.com/in/elisa-renard-test' WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'elisa.renard@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Farid', 'Colin', 'Ancien étudiant', 'https://example.com/farid-colin', 'farid.colin@example.com', NULL WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'farid.colin@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Gaelle', 'Marchand', 'Ancien étudiant', 'https://example.com/gaelle-marchand', 'gaelle.marchand@example.com', 'https://www.linkedin.com/in/gaelle-marchand-test' WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'gaelle.marchand@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Hugo', 'Perrin', 'Ancien étudiant', 'https://example.com/hugo-perrin', 'hugo.perrin@example.com', NULL WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'hugo.perrin@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Imane', 'Roussel', 'Ancien étudiant', 'https://example.com/imane-roussel', 'imane.roussel@example.com', 'https://www.linkedin.com/in/imane-roussel-test' WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'imane.roussel@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Jules', 'Fontaine', 'Ancien étudiant', 'https://example.com/jules-fontaine', 'jules.fontaine@example.com', NULL WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'jules.fontaine@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Karima', 'Blanc', 'Ancien étudiant', 'https://example.com/karima-blanc', 'karima.blanc@example.com', 'https://www.linkedin.com/in/karima-blanc-test' WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'karima.blanc@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Loris', 'Meunier', 'Ancien étudiant', 'https://example.com/loris-meunier', 'loris.meunier@example.com', NULL WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'loris.meunier@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Maeva', 'Picard', 'Ancien étudiant', 'https://example.com/maeva-picard', 'maeva.picard@example.com', 'https://www.linkedin.com/in/maeva-picard-test' WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'maeva.picard@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Nassim', 'Guillot', 'Ancien étudiant', 'https://example.com/nassim-guillot', 'nassim.guillot@example.com', NULL WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'nassim.guillot@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Oceane', 'Lambert', 'Ancien étudiant', 'https://example.com/oceane-lambert', 'oceane.lambert@example.com', 'https://www.linkedin.com/in/oceane-lambert-test' WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'oceane.lambert@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Paul', 'Aubert', 'Ancien étudiant', 'https://example.com/paul-aubert', 'paul.aubert@example.com', NULL WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'paul.aubert@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Rania', 'Delmas', 'Ancien étudiant', 'https://example.com/rania-delmas', 'rania.delmas@example.com', 'https://www.linkedin.com/in/rania-delmas-test' WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'rania.delmas@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Sacha', 'Vidal', 'Ancien étudiant', 'https://example.com/sacha-vidal', 'sacha.vidal@example.com', NULL WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'sacha.vidal@example.com')");
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url, email, linkedin_url) SELECT 'Tania', 'Leroy-Simon', 'Ancien étudiant', 'https://example.com/tania-leroy-simon', 'tania.leroy-simon-long-test@example.com', 'https://www.linkedin.com/in/tania-leroy-simon-test' WHERE NOT EXISTS (SELECT 1 FROM portfolio WHERE email = 'tania.leroy-simon-long-test@example.com')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM portfolio WHERE email IN ('adel.bourgeois@example.com', 'aline.chevalier@example.com', 'bastien.lemoine@example.com', 'chloe.garnier@example.com', 'dorian.masson@example.com', 'elisa.renard@example.com', 'farid.colin@example.com', 'gaelle.marchand@example.com', 'hugo.perrin@example.com', 'imane.roussel@example.com', 'jules.fontaine@example.com', 'karima.blanc@example.com', 'loris.meunier@example.com', 'maeva.picard@example.com', 'nassim.guillot@example.com', 'oceane.lambert@example.com', 'paul.aubert@example.com', 'rania.delmas@example.com', 'sacha.vidal@example.com', 'tania.leroy-simon-long-test@example.com')");
    }
}
