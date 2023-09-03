<?php

namespace App\Entity;

use App\Repository\ButApprentissageCritiqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ButApprentissageCritiqueRepository::class)]
class ButApprentissageCritique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(inversedBy: 'butApprentissageCritiques')]
    private ?ButNiveau $niveau = null;

    #[ORM\ManyToMany(targetEntity: ElementConstitutif::class, mappedBy: 'apprentissagesCritiques')]
    private Collection $elementConstitutifs;

    public function __construct()
    {
        $this->elementConstitutifs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
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

    public function getNiveau(): ?ButNiveau
    {
        return $this->niveau;
    }

    public function setNiveau(?ButNiveau $niveau): self
    {
        $this->niveau = $niveau;

        return $this;
    }

    /**
     * @return Collection<int, ElementConstitutif>
     */
    public function getElementConstitutifs(): Collection
    {
        return $this->elementConstitutifs;
    }

    public function addElementConstitutif(ElementConstitutif $elementConstitutif): static
    {
        if (!$this->elementConstitutifs->contains($elementConstitutif)) {
            $this->elementConstitutifs->add($elementConstitutif);
            $elementConstitutif->addApprentissagesCritique($this);
        }

        return $this;
    }

    public function removeElementConstitutif(ElementConstitutif $elementConstitutif): static
    {
        if ($this->elementConstitutifs->removeElement($elementConstitutif)) {
            $elementConstitutif->removeApprentissagesCritique($this);
        }

        return $this;
    }
}
