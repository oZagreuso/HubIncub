<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612134000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renseigner des codes postaux de démonstration pour la carte membres';
    }

    public function up(Schema $schema): void
    {
        $locations = [
            'olivier@dal-ferro.com' => '57200',
            'camille.martin@example.com' => '54000',
            'yanis.bernard@example.com' => '67000',
            'lea.petit@example.com' => '75000',
            'noah.morel-dupont@example.com' => '33000',
            'ines.roche.long-email-test@example.com' => '31000',
            'adel.bourgeois@example.com' => '69000',
            'aline.chevalier@example.com' => '59000',
            'bastien.lemoine@example.com' => '44000',
            'chloe.garnier@example.com' => '13000',
            'dorian.masson@example.com' => '35000',
            'elisa.renard@example.com' => '21000',
            'farid.colin@example.com' => '63000',
            'gaelle.marchand@example.com' => 'L-1234',
            'hugo.perrin@example.com' => 'L-2449',
            'imane.roussel@example.com' => '57000',
            'jules.fontaine@example.com' => '67000',
            'karima.blanc@example.com' => '34000',
            'loris.meunier@example.com' => '06000',
            'maeva.picard@example.com' => '54000',
            'nassim.guillot@example.com' => 'L-4132',
            'oceane.lambert@example.com' => '29000',
            'paul.aubert@example.com' => '76000',
            'rania.delmas@example.com' => '38000',
            'sacha.vidal@example.com' => '86000',
            'tania.leroy-simon-long-test@example.com' => '64000',
        ];

        foreach ($locations as $email => $postalCode) {
            $this->addSql(sprintf(
                "UPDATE portfolio SET postal_code = '%s' WHERE email = '%s'",
                $postalCode,
                $email,
            ));
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE portfolio SET postal_code = NULL WHERE email = 'olivier@dal-ferro.com' OR email LIKE '%@example.com'");
    }
}
