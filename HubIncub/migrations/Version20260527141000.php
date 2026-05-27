<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527141000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add news managed from administration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE news (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(150) NOT NULL, content CLOB NOT NULL, published_at DATETIME NOT NULL, image_filename VARCHAR(255) DEFAULT NULL, image_alt VARCHAR(180) DEFAULT NULL)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE news');
    }
}
