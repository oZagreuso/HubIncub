<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612124000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Associer des avatars aux membres fictifs';
    }

    public function up(Schema $schema): void
    {
        $avatars = [
            'camille.martin@example.com' => 'camille-martin-avatar.jpg',
            'yanis.bernard@example.com' => 'yanis-bernard-avatar.jpg',
            'lea.petit@example.com' => 'lea-petit-avatar.jpg',
            'noah.morel-dupont@example.com' => 'noah-morel-dupont-avatar.jpg',
            'ines.roche.long-email-test@example.com' => 'ines-roche-avatar.jpg',
            'adel.bourgeois@example.com' => 'adel-bourgeois-avatar.jpg',
            'aline.chevalier@example.com' => 'aline-chevalier-avatar.jpg',
            'bastien.lemoine@example.com' => 'bastien-lemoine-avatar.jpg',
            'chloe.garnier@example.com' => 'chloe-garnier-avatar.jpg',
            'dorian.masson@example.com' => 'dorian-masson-avatar.jpg',
            'elisa.renard@example.com' => 'elisa-renard-avatar.jpg',
            'farid.colin@example.com' => 'farid-colin-avatar.jpg',
            'gaelle.marchand@example.com' => 'gaelle-marchand-avatar.jpg',
            'hugo.perrin@example.com' => 'hugo-perrin-avatar.jpg',
            'imane.roussel@example.com' => 'imane-roussel-avatar.jpg',
            'jules.fontaine@example.com' => 'jules-fontaine-avatar.jpg',
            'karima.blanc@example.com' => 'karima-blanc-avatar.jpg',
            'loris.meunier@example.com' => 'loris-meunier-avatar.jpg',
            'maeva.picard@example.com' => 'maeva-picard-avatar.jpg',
            'nassim.guillot@example.com' => 'nassim-guillot-avatar.jpg',
            'oceane.lambert@example.com' => 'oceane-lambert-avatar.jpg',
            'paul.aubert@example.com' => 'paul-aubert-avatar.jpg',
            'rania.delmas@example.com' => 'rania-delmas-avatar.jpg',
            'sacha.vidal@example.com' => 'sacha-vidal-avatar.jpg',
            'tania.leroy-simon-long-test@example.com' => 'tania-leroy-simon-avatar.jpg',
        ];

        foreach ($avatars as $email => $filename) {
            $this->addSql(sprintf(
                "UPDATE portfolio SET photo_filename = '%s' WHERE email = '%s'",
                $filename,
                $email,
            ));
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE portfolio SET photo_filename = NULL WHERE email LIKE '%@example.com'");
    }
}
