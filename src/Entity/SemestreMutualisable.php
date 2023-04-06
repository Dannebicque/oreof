<?php

namespace App\Entity;

use App\Repository\SemestreMutualisableRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\OneToMany(mappedBy: 'semestreRaccroche', targetEntity: SemestreParcours::class)]
    private Collection $semestreParcours;

    public function __construct()
    {
        $this->semestreParcours = new ArrayCollection();
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

    /**
     * @return Collection<int, SemestreParcours>
     */
    public function getSemestreParcours(): Collection
    {
        return $this->semestreParcours;
    }

    public function addSemestreParcour(SemestreParcours $semestreParcour): self
    {
        if (!$this->semestreParcours->contains($semestreParcour)) {
            $this->semestreParcours->add($semestreParcour);
            $semestreParcour->setSemestreRaccroche($this);
        }

        return $this;
    }

    public function removeSemestreParcour(SemestreParcours $semestreParcour): self
    {
        if ($this->semestreParcours->removeElement($semestreParcour)) {
            // set the owning side to null (unless already changed)
            if ($semestreParcour->getSemestreRaccroche() === $this) {
                $semestreParcour->setSemestreRaccroche(null);
            }
        }

        return $this;
    }
}
