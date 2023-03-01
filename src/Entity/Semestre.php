<?php

namespace App\Entity;

use App\Repository\SemestreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SemestreRepository::class)]
class Semestre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $ordre = null;

    #[ORM\OneToMany(mappedBy: 'semestre', targetEntity: Ue::class)]
    private Collection $ues;

    #[ORM\OneToMany(mappedBy: 'semestre', targetEntity: SemestreParcours::class)]
    private Collection $semestreParcours;

    #[ORM\Column]
    private ?bool $troncCommun = false;

    public function __construct()
    {
        $this->ues = new ArrayCollection();
        $this->semestreParcours = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, Ue>
     */
    public function getUes(): Collection
    {
        return $this->ues;
    }

    public function addUe(Ue $ue): self
    {
        if (!$this->ues->contains($ue)) {
            $this->ues->add($ue);
            $ue->setSemestre($this);
        }

        return $this;
    }

    public function removeUe(Ue $ue): self
    {
        // set the owning side to null (unless already changed)
        if ($this->ues->removeElement($ue) && $ue->getSemestre() === $this) {
            $ue->setSemestre(null);
        }

        return $this;
    }

    public function display(): string
    {
        return 'S'.$this->getOrdre();
    }

    public function totalEctsSemestre(): int
    {
        $total = 0;
        foreach ($this->getUes() as $ue) {
            $total += $ue->totalEctsUe();
        }

        return $total;
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
            $semestreParcour->setSemestre($this);
        }

        return $this;
    }

    public function removeSemestreParcour(SemestreParcours $semestreParcour): self
    {
        // set the owning side to null (unless already changed)
        if ($this->semestreParcours->removeElement($semestreParcour) && $semestreParcour->getSemestre() === $this) {
            $semestreParcour->setSemestre(null);
        }

        return $this;
    }

    public function isTroncCommun(): ?bool
    {
        return $this->troncCommun;
    }

    public function setTroncCommun(bool $troncCommun): self
    {
        $this->troncCommun = $troncCommun;

        return $this;
    }
}
