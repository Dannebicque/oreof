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
    private ?string $parcoursFileName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dtoFileName = null;

    #[ORM\Column(nullable: true)]
    private ?bool $cvfuFlag = null;

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

    public function getParcoursFileName(): ?string
    {
        return $this->parcoursFileName;
    }

    public function setParcoursFileName(string $fileName): static
    {
        $this->parcoursFileName = $fileName;

        return $this;
    }

    public function getDtoFileName(): ?string
    {
        return $this->dtoFileName;
    }

    public function setDtoFileName(?string $dtoFileName): static
    {
        $this->dtoFileName = $dtoFileName;

        return $this;
    }

    public function isCvfuFlag(): ?bool
    {
        return $this->cvfuFlag;
    }

    public function setCvfuFlag(?bool $cvfuFlag): static
    {
        $this->cvfuFlag = $cvfuFlag;

        return $this;
    }
}
