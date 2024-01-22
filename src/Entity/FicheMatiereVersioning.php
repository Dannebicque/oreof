<?php

namespace App\Entity;

use App\Repository\FicheMatiereVersioningRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FicheMatiereVersioningRepository::class)]
class FicheMatiereVersioning
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'ficheMatiereVersionings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FicheMatiere $ficheMatiere = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $version_timestamp = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFicheMatiere(): ?FicheMatiere
    {
        return $this->ficheMatiere;
    }

    public function setFicheMatiere(?FicheMatiere $ficheMatiere): static
    {
        $this->ficheMatiere = $ficheMatiere;

        return $this;
    }

    public function getVersionTimestamp(): ?\DateTimeImmutable
    {
        return $this->version_timestamp;
    }

    public function setVersionTimestamp(?\DateTimeImmutable $version_timestamp): static
    {
        $this->version_timestamp = $version_timestamp;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }
}
