<?php

namespace App\Entity;

use App\Repository\SemestreParcoursRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SemestreParcoursRepository::class)]
class SemestreParcours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\ManyToOne(inversedBy: 'semestreParcours')]
    private ?Semestre $semestre = null;

    #[ORM\ManyToOne(inversedBy: 'semestreParcours')]
    private ?Parcours $parcours = null;

    public function __construct(Semestre $semestre, Parcours $parcours)
    {
        $this->setSemestre($semestre);
        $this->setParcours($parcours);
    }

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
}
