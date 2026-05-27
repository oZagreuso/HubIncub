<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527151000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add optional LinkedIn profile URL to members';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE portfolio ADD COLUMN linkedin_url VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__portfolio AS SELECT id, first_name, last_name, role, url, email FROM portfolio');
        $this->addSql('DROP TABLE portfolio');
        $this->addSql('CREATE TABLE portfolio (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, role VARCHAR(150) NOT NULL, url VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL)');
        $this->addSql('INSERT INTO portfolio (id, first_name, last_name, role, url, email) SELECT id, first_name, last_name, role, url, email FROM __temp__portfolio');
        $this->addSql('DROP TABLE __temp__portfolio');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_PORTFOLIO_EMAIL ON portfolio (email)');
    }
}
