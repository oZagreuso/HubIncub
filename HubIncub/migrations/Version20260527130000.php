<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajouter les utilisateurs administrateurs, les projets et les événements';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
        $this->addSql('CREATE TABLE project (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(150) NOT NULL, description CLOB NOT NULL, url VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE TABLE event (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(150) NOT NULL, starts_at DATETIME DEFAULT NULL, description CLOB NOT NULL)');
        $this->addSql('INSERT INTO user (email, roles, password) VALUES (\'olivier@dal-ferro.com\', \'["ROLE_ADMIN"]\', \'$2y$12$ZetrQvmwZ3B/IJqFTdvno.tnGHoCva0fnqHQ0gqs4r3hVE0xarT6m\')');
        $this->addSql("UPDATE portfolio SET email = 'olivier@dal-ferro.com' WHERE first_name = 'Olivier' AND last_name = 'Dal Ferro'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE event');
    }
}
