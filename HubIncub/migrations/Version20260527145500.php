<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527145500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize member statuses and set Olivier as incubator admin';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE portfolio SET role = 'Incubateur' WHERE email = 'olivier@dal-ferro.com'");
        $this->addSql("UPDATE portfolio SET role = 'Ancien étudiant' WHERE role NOT IN ('Incubateur', 'Ancien étudiant')");
        $this->addSql("UPDATE user SET roles = '[\"ROLE_USER\"]' WHERE email <> 'olivier@dal-ferro.com'");
        $this->addSql("UPDATE user SET roles = '[\"ROLE_ADMIN\"]' WHERE email = 'olivier@dal-ferro.com'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE portfolio SET role = 'Ancien étudiant' WHERE email = 'olivier@dal-ferro.com'");
    }
}
