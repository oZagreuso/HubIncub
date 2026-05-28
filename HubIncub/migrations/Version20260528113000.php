<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260528113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize sample portfolio role accents';
    }

    public function up(Schema $schema): void
    {
        // Normalizes local sample data created from terminals that replaced accented characters.
        $this->addSql("UPDATE portfolio SET role = 'Ancien étudiant' WHERE role = 'Ancien ?tudiant'");
    }

    public function down(Schema $schema): void
    {
    }
}
