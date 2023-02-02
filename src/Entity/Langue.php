<?php

namespace App\Entity;

use App\Repository\LangueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LangueRepository::class)]
class Langue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $libelle = null;

    #[ORM\ManyToMany(targetEntity: ElementConstitutif::class, mappedBy: 'langueDispense')]
    private Collection $elementConstitutifs;

    #[ORM\ManyToMany(targetEntity: ElementConstitutif::class, mappedBy: 'langueSupport')]
    private Collection $languesSupportsEcs;

    #[ORM\Column(length: 2)]
    private ?string $codeIso = null;

    public function __construct()
    {
        $this->elementConstitutifs = new ArrayCollection();
        $this->languesSupportsEcs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

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
            $elementConstitutif->addLangueDispense($this);
        }

        return $this;
    }

    public function removeElementConstitutif(ElementConstitutif $elementConstitutif): self
    {
        if ($this->elementConstitutifs->removeElement($elementConstitutif)) {
            $elementConstitutif->removeLangueDispense($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, ElementConstitutif>
     */
    public function getLanguesSupportsEcs(): Collection
    {
        return $this->languesSupportsEcs;
    }

    public function addLanguesSupportsEc(ElementConstitutif $languesSupportsEc): self
    {
        if (!$this->languesSupportsEcs->contains($languesSupportsEc)) {
            $this->languesSupportsEcs->add($languesSupportsEc);
            $languesSupportsEc->addLangueSupport($this);
        }

        return $this;
    }

    public function removeLanguesSupportsEc(ElementConstitutif $languesSupportsEc): self
    {
        if ($this->languesSupportsEcs->removeElement($languesSupportsEc)) {
            $languesSupportsEc->removeLangueSupport($this);
        }

        return $this;
    }

    public function getCodeIso(): ?string
    {
        return $this->codeIso;
    }

    public function setCodeIso(string $codeIso): self
    {
        $this->codeIso = $codeIso;

        return $this;
    }
}
