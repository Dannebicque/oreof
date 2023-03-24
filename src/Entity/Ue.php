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

    #[ORM\OneToMany(mappedBy: 'ue', targetEntity: ElementConstitutif::class)]
    private Collection $elementConstitutifs;

    #[ORM\Column(nullable: true)]
    private ?int $subOrdre = null;

    public function __construct()
    {
        $this->ecUes = new ArrayCollection();
        $this->elementConstitutifs = new ArrayCollection();
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
        if ($this->subOrdre === null || $this->subOrdre === 0) {
            $ordreue =  $this->ordre;
        } else {
            $ordreue = $this->ordre . '.' . chr($this->subOrdre + 64);
        }

        return 'UE ' . $this->getSemestre()?->getOrdre() .'.'.$ordreue;
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

    public function getSubOrdre(): ?int
    {
        return $this->subOrdre;
    }

    public function setSubOrdre(?int $subOrdre): self
    {
        $this->subOrdre = $subOrdre;

        return $this;
    }
}
