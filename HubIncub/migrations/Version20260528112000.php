<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260528112000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Définir Maeva Picard comme utilisatrice déléguée';
    }

    public function up(Schema $schema): void
    {
        // Le compte délégué est requis car la priorité de l'annuaire est résolue depuis les rôles User.
        $this->addSql("INSERT INTO user (email, roles, password) SELECT 'maeva.picard@example.com', '[\"ROLE_DELEGATE\"]', 'disabled-delegate-test-account' WHERE NOT EXISTS (SELECT 1 FROM user WHERE email = 'maeva.picard@example.com')");
        $this->addSql("UPDATE user SET roles = '[\"ROLE_DELEGATE\"]' WHERE email = 'maeva.picard@example.com'");
        $this->addSql("UPDATE user SET roles = '[\"ROLE_ADMIN\"]' WHERE email = 'olivier@dal-ferro.com'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM user WHERE email = 'maeva.picard@example.com' AND password = 'disabled-delegate-test-account'");
    }
}
