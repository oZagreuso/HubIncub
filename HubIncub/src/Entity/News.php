<?php

namespace App\Entity;

use App\Repository\NewsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Actualité publiée depuis l'administration.
 *
 * La dernière actualité publiée alimente automatiquement la page d'accueil.
 * Les images associées sont stockées sur disque ; l'entité conserve uniquement
 * le nom du fichier et le texte alternatif utilisé dans les vues.
 */
#[ORM\Entity(repositoryClass: NewsRepository::class)]
class News
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private string $title = '';

    #[ORM\Column(type: 'text')]
    private string $content = '';

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $publishedAt;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageFilename = null;

    /**
     * Texte alternatif SEO et accessibilité associé à l'image d'actualité.
     */
    #[ORM\Column(length: 180, nullable: true)]
    private ?string $imageAlt = null;

    public function __construct()
    {
        $this->publishedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getPublishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    public function setImageFilename(?string $imageFilename): self
    {
        $this->imageFilename = $imageFilename;

        return $this;
    }

    public function getImageAlt(): ?string
    {
        return $this->imageAlt;
    }

    public function setImageAlt(?string $imageAlt): self
    {
        $this->imageAlt = $imageAlt;

        return $this;
    }
}
