<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527134500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajouter le texte alternatif SEO aux images des projets et événements';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project ADD COLUMN image_alt VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD COLUMN image_alt VARCHAR(180) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__project AS SELECT id, name, description, url, image_filename FROM project');
        $this->addSql('DROP TABLE project');
        $this->addSql('CREATE TABLE project (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(150) NOT NULL, description CLOB NOT NULL, url VARCHAR(255) DEFAULT NULL, image_filename VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO project (id, name, description, url, image_filename) SELECT id, name, description, url, image_filename FROM __temp__project');
        $this->addSql('DROP TABLE __temp__project');

        $this->addSql('CREATE TEMPORARY TABLE __temp__event AS SELECT id, title, starts_at, description, image_filename FROM event');
        $this->addSql('DROP TABLE event');
        $this->addSql('CREATE TABLE event (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(150) NOT NULL, starts_at DATETIME DEFAULT NULL, description CLOB NOT NULL, image_filename VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO event (id, title, starts_at, description, image_filename) SELECT id, title, starts_at, description, image_filename FROM __temp__event');
        $this->addSql('DROP TABLE __temp__event');
    }
}
