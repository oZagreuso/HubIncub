<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612132000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajouter un code postal optionnel aux fiches membres';
    }

    public function up(Schema $schema): void
    {
        $columns = $this->connection->fetchFirstColumn("SELECT name FROM pragma_table_info('portfolio')");

        if (!in_array('postal_code', $columns, true)) {
            $this->addSql('ALTER TABLE portfolio ADD COLUMN postal_code VARCHAR(12) DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $columns = $this->connection->fetchFirstColumn("SELECT name FROM pragma_table_info('portfolio')");

        if (in_array('postal_code', $columns, true)) {
            $this->addSql('ALTER TABLE portfolio DROP COLUMN postal_code');
        }
    }
}
