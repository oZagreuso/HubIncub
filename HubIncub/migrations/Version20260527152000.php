<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527152000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set Olivier Dal Ferro LinkedIn profile URL';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE portfolio SET linkedin_url = 'https://www.linkedin.com/in/olivier-dal-ferro/' WHERE email = 'olivier@dal-ferro.com'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE portfolio SET linkedin_url = NULL WHERE email = 'olivier@dal-ferro.com' AND linkedin_url = 'https://www.linkedin.com/in/olivier-dal-ferro/'");
    }
}
