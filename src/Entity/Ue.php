<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/Ue.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 21:19
 */

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


    #[ORM\ManyToOne]
    private ?NatureUeEc $natureUeEc = null;

    #[ORM\OneToMany(mappedBy: 'ue', targetEntity: ElementConstitutif::class, cascade: [
        'persist',
        'remove'
    ], orphanRemoval: true)]
    #[ORM\OrderBy(['ordre' => 'ASC'])]
    private Collection $elementConstitutifs;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $libelle = null;

    #[ORM\OneToMany(mappedBy: 'ue', targetEntity: UeMutualisable::class)]
    private Collection $ueMutualisables;

    #[ORM\ManyToOne(inversedBy: 'ues')]
    private ?UeMutualisable $ueRaccrochee = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'ueEnfants')]
    private ?self $ueParent = null;

    #[ORM\OneToMany(mappedBy: 'ueParent', targetEntity: self::class, cascade: [
        'persist',
        'remove'
    ], orphanRemoval: true)]
    #[ORM\OrderBy(['ordre' => 'ASC'])]
    private Collection $ueEnfants;

    public function __construct()
    {
        $this->elementConstitutifs = new ArrayCollection();
        $this->ueMutualisables = new ArrayCollection();
        $this->ueEnfants = new ArrayCollection();
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
        if ($this->ueParent === null) {
            $ordreue = $this->ordre;
        } else {
            $ordreue = $this->ueParent->ordre . '.' . chr($this->ordre + 64);
        }

        return 'UE ' . $this->getSemestre()?->getOrdre() . '.' . $ordreue;
    }

    public function displayBouton(): string
    {
        if ($this->ueParent === null) {
            $ordreue = $this->ordre;
        } else {
            $ordreue = $this->ueParent->ordre . '.' . chr($this->ordre + 64);
        }
        return 'UE ' . $this->getSemestre()?->getOrdre() . '.' . $ordreue;
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

    public function totalEctsUe(): int
    {
        $total = 0;
        foreach ($this->getElementConstitutifs() as $ec) {
            $total += $ec->getEcts();
        }

        return $total;
    }

    public function getNatureUeEc(): ?NatureUeEc
    {
        return $this->natureUeEc;
    }

    public function setNatureUeEc(?NatureUeEc $natureUeEc): self
    {
        $this->natureUeEc = $natureUeEc;

        return $this;
    }

    /**
     * @return Collection<int, ElementConstitutif>
     */
    public function getElementConstitutifs(): Collection
    {
        return $this->elementConstitutifs;
    }

    public function addElementConstitutif(ElementConstitutif $elementConstitutif): self
    {
        if (!$this->elementConstitutifs->contains($elementConstitutif)) {
            $this->elementConstitutifs->add($elementConstitutif);
            $elementConstitutif->setUe($this);
        }

        return $this;
    }

    public function removeElementConstitutif(ElementConstitutif $elementConstitutif): self
    {
        if ($this->elementConstitutifs->removeElement($elementConstitutif)) {
            // set the owning side to null (unless already changed)
            if ($elementConstitutif->getUe() === $this) {
                $elementConstitutif->setUe(null);
            }
        }

        return $this;
    }

    public function nbElementConstitutifs(): int
    {
        $tabElement = [];

        foreach ($this->getElementConstitutifs() as $elementConstitutif) {
            $tabElement[$elementConstitutif->getOrdre()] = 1;
        }

        return count($tabElement);
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, UeMutualisable>
     */
    public function getUeMutualisables(): Collection
    {
        return $this->ueMutualisables;
    }

    public function addUeMutualisable(UeMutualisable $ueMutualisable): self
    {
        if (!$this->ueMutualisables->contains($ueMutualisable)) {
            $this->ueMutualisables->add($ueMutualisable);
            $ueMutualisable->setUe($this);
        }

        return $this;
    }

    public function removeUeMutualisable(UeMutualisable $ueMutualisable): self
    {
        if ($this->ueMutualisables->removeElement($ueMutualisable)) {
            // set the owning side to null (unless already changed)
            if ($ueMutualisable->getUe() === $this) {
                $ueMutualisable->setUe(null);
            }
        }

        return $this;
    }

    public function getUeRaccrochee(): ?UeMutualisable
    {
        return $this->ueRaccrochee;
    }

    public function setUeRaccrochee(?UeMutualisable $ueRaccrochee): self
    {
        $this->ueRaccrochee = $ueRaccrochee;

        return $this;
    }

    public function getUeParent(): ?self
    {
        return $this->ueParent;
    }

    public function setUeParent(?self $ueParent): self
    {
        $this->ueParent = $ueParent;

        return $this;
    }

    /**
     * @return Collection<int, Ue>
     */
    public function getUeEnfants(): Collection
    {
        return $this->ueEnfants;
    }

    public function addUeEnfant(Ue $ueEnfant): self
    {
        if (!$this->ueEnfants->contains($ueEnfant)) {
            $this->ueEnfants->add($ueEnfant);
            $ueEnfant->setUeParent($this);
        }

        return $this;
    }

    public function removeUeEnfant(Ue $ueEnfant): self
    {
        if ($this->ueEnfants->removeElement($ueEnfant)) {
            // set the owning side to null (unless already changed)
            if ($ueEnfant->getUeParent() === $this) {
                $ueEnfant->setUeParent(null);
            }
        }

        return $this;
    }
}
