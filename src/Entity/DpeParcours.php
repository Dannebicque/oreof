<?php

namespace App\Entity;

use App\Enums\TypeModificationDpeEnum;
use App\Repository\DpeParcoursRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DpeParcoursRepository::class)]
class DpeParcours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'dpeParcours')]
    private ?Dpe $dpe = null;

    #[ORM\ManyToOne(inversedBy: 'dpeParcours')]
    private ?Parcours $parcours = null;

    #[ORM\Column]
    private array $etatValidation = [];

    #[ORM\Column(length: 10)]
    private ?string $version = null;

    #[ORM\Column(length: 255, enumType: TypeModificationDpeEnum::class)]
    private ?TypeModificationDpeEnum $etatReconduction = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created = null;

    #[ORM\ManyToOne(inversedBy: 'dpeParcours')]
    private ?Formation $formation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDpe(): ?Dpe
    {
        return $this->dpe;
    }

    public function setDpe(?Dpe $dpe): static
    {
        $this->dpe = $dpe;

        return $this;
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

    public function getEtatValidation(): array
    {
        return $this->etatValidation ?? [];
    }

    public function setEtatValidation(array $etatValidation): static
    {
        $this->etatValidation = $etatValidation;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getEtatReconduction(): ?TypeModificationDpeEnum
    {
        return $this->etatReconduction;
    }

    public function setEtatReconduction(TypeModificationDpeEnum $etatReconduction): static
    {
        $this->etatReconduction = $etatReconduction;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): static
    {
        $this->created = $created;

        return $this;
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
}
