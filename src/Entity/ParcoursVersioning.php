<?php

namespace App\Entity;

use App\Repository\ParcoursVersioningRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParcoursVersioningRepository::class)]
class ParcoursVersioning
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'parcoursVersionings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Parcours $parcours = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $version_timestamp = null;

    #[ORM\Column(length: 255)]
    private ?string $fileName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParcours(): ?Parcours
    {
        return $this->parcours;
    }

    public function setParcours(?Parcours $parcours): static
    {
        $this->parcours = $parcours;

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

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }
}