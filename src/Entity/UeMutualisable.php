<?php

namespace App\Entity;

use App\Repository\UeMutualisableRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UeMutualisableRepository::class)]
class UeMutualisable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'ueMutualisables')]
    private ?Ue $ue = null;

    #[ORM\ManyToOne(inversedBy: 'ueMutualisables')]
    private ?Parcours $parcours = null;

    #[ORM\Column]
    private ?bool $isPorteur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUe(): ?Ue
    {
        return $this->ue;
    }

    public function setUe(?Ue $ue): self
    {
        $this->ue = $ue;

        return $this;
    }

    public function getParcours(): ?Parcours
    {
        return $this->parcours;
    }

    public function setParcours(?Parcours $parcours): self
    {
        $this->parcours = $parcours;

        return $this;
    }

    public function isIsPorteur(): ?bool
    {
        return $this->isPorteur;
    }

    public function setIsPorteur(bool $isPorteur): self
    {
        $this->isPorteur = $isPorteur;

        return $this;
    }
}
