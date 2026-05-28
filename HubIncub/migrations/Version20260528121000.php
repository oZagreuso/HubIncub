<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260528121000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add sample HubIncub clothing line project';
    }

    public function up(Schema $schema): void
    {
        // Sample project used to validate the public project listing with editorial content.
        $this->addSql("INSERT INTO project (name, description, url, image_filename, image_alt) SELECT 'HubWear', 'Projet fictif de création d’une ligne de vêtements réservée aux membres HubIncub : sweats, t-shirts et accessoires sobres aux couleurs du réseau, pensés pour les événements, les promotions et les rencontres alumni.', NULL, 'hubwear-ligne-vetements-hubincub.png', 'Logo HubIncub utilisé comme visuel du projet fictif HubWear' WHERE NOT EXISTS (SELECT 1 FROM project WHERE name = 'HubWear')");
        $this->addSql("UPDATE project SET image_filename = 'hubwear-ligne-vetements-hubincub.png', image_alt = 'Logo HubIncub utilisé comme visuel du projet fictif HubWear' WHERE name = 'HubWear'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM project WHERE name = 'HubWear'");
    }
}
