<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/SemestreParcours.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 02/02/2023 21:21
 */

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

    #[ORM\Column]
    private ?int $ordre = 0;

    #[ORM\Column]
    private ?bool $porteur = false;

    #[ORM\ManyToOne(inversedBy: 'semestreParcours')]
    private ?SemestreMutualisable $semestreRaccroche = null;

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

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function isPorteur(): ?bool
    {
        return $this->porteur;
    }

    public function setPorteur(bool $porteur): self
    {
        $this->porteur = $porteur;

        return $this;
    }

    public function getSemestreRaccroche(): ?SemestreMutualisable
    {
        return $this->semestreRaccroche;
    }

    public function setSemestreRaccroche(?SemestreMutualisable $semestreRaccroche): self
    {
        $this->semestreRaccroche = $semestreRaccroche;

        return $this;
    }
}
