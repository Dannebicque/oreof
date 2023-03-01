<?php

namespace App\Entity;

use App\Repository\UeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UeRepository::class)]
class Ue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $ordre = null;

    #[ORM\ManyToOne(inversedBy: 'ues')]
    private ?Semestre $semestre = null;

    #[ORM\ManyToOne]
    private ?TypeUe $typeUe = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $typeUeTexte = null;

    #[ORM\OneToMany(mappedBy: 'ue', targetEntity: EcUe::class)]
    private Collection $ecUes;

    #[ORM\ManyToOne]
    private ?TypeEnseignement $ueObligatoire = null;

    public function __construct()
    {
        $this->ecUes = new ArrayCollection();
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

    public function getSemestre(): ?Semestre
    {
        return $this->semestre;
    }

    public function setSemestre(?Semestre $semestre): self
    {
        $this->semestre = $semestre;

        return $this;
    }

    public function display(): string
    {
        return 'UE ' . $this->getSemestre()?->getOrdre() .'.'.$this->getOrdre();
    }

    public function getTypeUe(): ?TypeUe
    {
        return $this->typeUe;
    }

    public function setTypeUe(?TypeUe $typeUe): self
    {
        $this->typeUe = $typeUe;

        return $this;
    }

    public function getTypeUeTexte(): ?string
    {
        return $this->typeUeTexte;
    }

    public function setTypeUeTexte(?string $typeUeTexte): self
    {
        $this->typeUeTexte = $typeUeTexte;

        return $this;
    }

    public function totalEctsUe(): int
    {
        $total = 0;
        foreach ($this->getEcUes() as $ecUe) {
            $total += $ecUe->getEc()?->getEcts();
        }

        return $total;
    }

    /**
     * @return Collection<int, EcUe>
     */
    public function getEcUes(): Collection
    {
        return $this->ecUes;
    }

    public function addEcUe(EcUe $ecUe): self
    {
        if (!$this->ecUes->contains($ecUe)) {
            $this->ecUes->add($ecUe);
            $ecUe->setUe($this);
        }

        return $this;
    }

    public function removeEcUe(EcUe $ecUe): self
    {
        // set the owning side to null (unless already changed)
        if ($this->ecUes->removeElement($ecUe) && $ecUe->getUe() === $this) {
            $ecUe->setUe(null);
        }

        return $this;
    }

    public function getUeObligatoire(): ?TypeEnseignement
    {
        return $this->ueObligatoire;
    }

    public function setUeObligatoire(?TypeEnseignement $ueObligatoire): self
    {
        $this->ueObligatoire = $ueObligatoire;

        return $this;
    }
}
