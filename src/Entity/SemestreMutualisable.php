<?php

namespace App\Entity;

use App\Repository\SemestreMutualisableRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SemestreMutualisableRepository::class)]
class SemestreMutualisable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'semestreMutualisables')]
    private ?Semestre $semestre = null;

    #[ORM\ManyToOne(inversedBy: 'semestreMutualisables')]
    private ?Parcours $parcours = null;

    #[ORM\Column]
    private ?bool $isPorteur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSemestre(): ?Semestre
    {
        return $this->semestre;
    }

    public function setSemestre(?Semestre $semestre): self
    {
        $this->semestre = $semestre;

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
