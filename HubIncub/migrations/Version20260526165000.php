<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260526165000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create portfolio table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE portfolio (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, role VARCHAR(150) NOT NULL, url VARCHAR(255) NOT NULL)');
        $this->addSql("INSERT INTO portfolio (first_name, last_name, role, url) VALUES ('Olivier', 'Dal Ferro', 'Ancien étudiant', 'https://dal-ferro.com')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE portfolio');
    }
}
