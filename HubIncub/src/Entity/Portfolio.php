<?php

namespace App\Entity;

use App\Repository\PortfolioRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Fiche membre affichée dans l'annuaire des anciens.
 *
 * L'entité porte les informations publiques du membre, la photo affichée dans
 * les cards et le code postal facultatif utilisé pour la carte interactive.
 */
#[ORM\Entity(repositoryClass: PortfolioRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_PORTFOLIO_EMAIL', fields: ['email'])]
class Portfolio
{
    public const ROLE_INCUBATOR = 'Incubateur';
    public const ROLE_ALUMNI = 'Ancien étudiant';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $firstName = '';

    #[ORM\Column(length: 100)]
    private string $lastName = '';

    /**
     * Statut métier affiché dans l'annuaire.
     */
    #[ORM\Column(length: 150)]
    private string $role = '';

    #[ORM\Column(length: 255)]
    private string $url = '';

    #[ORM\Column(length: 180)]
    private string $email = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkedinUrl = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $promotion = null;

    /**
     * Nom du fichier image stocké dans public/uploads/portfolios.
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoFilename = null;

    /**
     * Code postal optionnel utilisé pour colorer la zone correspondante sur la carte.
     */
    #[ORM\Column(length: 12, nullable: true)]
    private ?string $postalCode = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        if (!in_array($role, [self::ROLE_INCUBATOR, self::ROLE_ALUMNI], true)) {
            throw new \InvalidArgumentException('Statut de portfolio invalide.');
        }

        $this->role = $role;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getLinkedinUrl(): ?string
    {
        return $this->linkedinUrl;
    }

    public function setLinkedinUrl(?string $linkedinUrl): self
    {
        $this->linkedinUrl = $linkedinUrl;

        return $this;
    }

    public function getPromotion(): ?string
    {
        return $this->promotion;
    }

    public function setPromotion(?string $promotion): self
    {
        $this->promotion = $promotion;

        return $this;
    }

    public function getPhotoFilename(): ?string
    {
        return $this->photoFilename;
    }

    public function setPhotoFilename(?string $photoFilename): self
    {
        $this->photoFilename = $photoFilename;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }
}
