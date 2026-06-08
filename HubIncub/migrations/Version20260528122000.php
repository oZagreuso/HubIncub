<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260528122000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajouter l'événement de test apéritif pour les anciens et nouveaux incubateurs";
    }

    public function up(Schema $schema): void
    {
        // Événement de test utilisé pour alimenter la liste publique et la carte du dernier événement en page d'accueil.
        $this->addSql("INSERT INTO event (title, starts_at, description, image_filename, image_alt) SELECT 'Apéritif anciens et nouveaux incubateurs', '2026-06-19 18:30:00', 'Rencontre conviviale entre les anciens et les nouveaux incubateurs HubIncub autour d’un apéritif réseau. L’événement permet de partager les expériences, présenter les projets en cours et créer les premiers contacts entre promotions.', 'aperitif-anciens-nouveaux-incubateurs.png', 'Illustration d’un apéritif réseau entre anciens et nouveaux incubateurs HubIncub' WHERE NOT EXISTS (SELECT 1 FROM event WHERE title = 'Apéritif anciens et nouveaux incubateurs')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM event WHERE title = 'Apéritif anciens et nouveaux incubateurs'");
    }
}
