<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajouter une photo optionnelle aux fiches membres';
    }

    public function up(Schema $schema): void
    {
        $columns = $this->connection->fetchFirstColumn("SELECT name FROM pragma_table_info('portfolio')");

        if (!in_array('photo_filename', $columns, true)) {
            $this->addSql('ALTER TABLE portfolio ADD COLUMN photo_filename VARCHAR(255) DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $columns = $this->connection->fetchFirstColumn("SELECT name FROM pragma_table_info('portfolio')");

        if (in_array('photo_filename', $columns, true)) {
            $this->addSql('ALTER TABLE portfolio DROP COLUMN photo_filename');
        }
    }
}
