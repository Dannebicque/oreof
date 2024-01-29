<?php

namespace App\Entity;

use App\Repository\AnneeUniversitaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnneeUniversitaireRepository::class)]
class AnneeUniversitaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $libelle = null;

    #[ORM\Column]
    private ?int $annee = null;

    #[ORM\OneToMany(mappedBy: 'annee_universitaire', targetEntity: CampagneCollecte::class)]
    private Collection $dpes;

    public function __construct()
    {
        $this->dpes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(int $annee): static
    {
        $this->annee = $annee;

        return $this;
    }

    /**
     * @return Collection<int, CampagneCollecte>
     */
    public function getDpes(): Collection
    {
        return $this->dpes;
    }

    public function addDpe(CampagneCollecte $dpe): static
    {
        if (!$this->dpes->contains($dpe)) {
            $this->dpes->add($dpe);
            $dpe->setAnneeUniversitaire($this);
        }

        return $this;
    }

    public function removeDpe(CampagneCollecte $dpe): static
    {
        if ($this->dpes->removeElement($dpe)) {
            // set the owning side to null (unless already changed)
            if ($dpe->getAnneeUniversitaire() === $this) {
                $dpe->setAnneeUniversitaire(null);
            }
        }

        return $this;
    }
}
