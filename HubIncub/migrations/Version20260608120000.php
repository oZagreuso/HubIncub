<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260608120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Suivre la présence des utilisateurs authentifiés';
    }

    public function up(Schema $schema): void
    {
        $columns = $this->connection->fetchFirstColumn("SELECT name FROM pragma_table_info('user')");

        if (!in_array('last_seen_at', $columns, true)) {
            $this->addSql('ALTER TABLE user ADD COLUMN last_seen_at DATETIME DEFAULT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $columns = $this->connection->fetchFirstColumn("SELECT name FROM pragma_table_info('user')");

        if (in_array('last_seen_at', $columns, true)) {
            $this->addSql('ALTER TABLE user DROP COLUMN last_seen_at');
        }
    }
}
