<?php

namespace App\Entity;

use App\Repository\AnneeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnneeRepository::class)]
class Annee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $ordre = null;

    #[ORM\ManyToOne(inversedBy: 'annees')]
    private ?Parcours $parcours = null;

    /**
     * @var Collection<int, SemestreParcours>
     */
    #[ORM\OneToMany(mappedBy: 'annee', targetEntity: SemestreParcours::class)]
    private Collection $parcoursSemestre;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $codeApogeeEtapeAnnee = null;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $codeApogeeEtapeVersion = null;

    #[ORM\Column]
    private ?bool $isOuvert = true;

    public function __construct()
    {
        $this->parcoursSemestre = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

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

    /**
     * @return Collection<int, SemestreParcours>
     */
    public function getParcoursSemestre(): Collection
    {
        return $this->parcoursSemestre;
    }

    public function addParcoursSemestre(SemestreParcours $parcoursSemestre): static
    {
        if (!$this->parcoursSemestre->contains($parcoursSemestre)) {
            $this->parcoursSemestre->add($parcoursSemestre);
            $parcoursSemestre->setAnnee($this);
        }

        return $this;
    }

    public function removeParcoursSemestre(SemestreParcours $parcoursSemestre): static
    {
        if ($this->parcoursSemestre->removeElement($parcoursSemestre)) {
            // set the owning side to null (unless already changed)
            if ($parcoursSemestre->getAnnee() === $this) {
                $parcoursSemestre->setAnnee(null);
            }
        }

        return $this;
    }

    public function getCodeApogeeEtapeAnnee(): ?string
    {
        return $this->codeApogeeEtapeAnnee;
    }

    public function setCodeApogeeEtapeAnnee(?string $codeApogeeEtapeAnnee): static
    {
        $this->codeApogeeEtapeAnnee = $codeApogeeEtapeAnnee;

        return $this;
    }

    public function getCodeApogeeEtapeVersion(): ?string
    {
        return $this->codeApogeeEtapeVersion;
    }

    public function setCodeApogeeEtapeVersion(?string $codeApogeeEtapeVersion): static
    {
        $this->codeApogeeEtapeVersion = $codeApogeeEtapeVersion;

        return $this;
    }

    public function isOuvert(): ?bool
    {
        return $this->isOuvert;
    }

    public function setIsOuvert(bool $isOuvert): static
    {
        $this->isOuvert = $isOuvert;

        return $this;
    }
}
