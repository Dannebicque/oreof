<?php

namespace App\Entity;

use App\Repository\FormationVersioningRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormationVersioningRepository::class)]
class FormationVersioning
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'formationVersionings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Formation $formation = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $version_timestamp = null;

    #[ORM\Column(length: 1000)]
    private ?string $filename = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    public function setFormation(?Formation $formation): static
    {
        $this->formation = $formation;

        return $this;
    }

    public function getVersionTimestamp(): ?\DateTimeImmutable
    {
        return $this->version_timestamp;
    }

    public function setVersionTimestamp(\DateTimeImmutable $version_timestamp): static
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
