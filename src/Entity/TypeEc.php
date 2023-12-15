<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/TypeUe.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 11/02/2023 07:51
 */

namespace App\Entity;

use App\Repository\TypeEcRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeEcRepository::class)]
class TypeEc
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $libelle = null;

    #[ORM\ManyToMany(targetEntity: TypeDiplome::class, inversedBy: 'typeEcs')]
    private Collection $typeDiplomes;

    #[ORM\OneToMany(mappedBy: 'typeEc', targetEntity: ElementConstitutif::class)]
    private Collection $elementConstitutifs;

    #[ORM\ManyToOne(inversedBy: 'typeEcs')]
    private ?Formation $formation = null;

    public function __construct()
    {
        $this->typeDiplomes = new ArrayCollection();
        $this->elementConstitutifs = new ArrayCollection();
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

//    public function getTypeDiplome(): array
//    {
//        return $this->typeDiplome ?? [];
//    }
//
//    public function setTypeDiplome(?array $typeDiplome): self
//    {
//        $this->typeDiplome = $typeDiplome;
//
//        return $this;
//    }

    /**
     * @return Collection|TypeDiplome[]
     */
    public function getTypeDiplomes(): Collection
    {
        return $this->typeDiplomes;
    }

    public function addTypeDiplome(TypeDiplome $typeDiplome): self
    {
        if (!$this->typeDiplomes->contains($typeDiplome)) {
            $this->typeDiplomes[] = $typeDiplome;
        }

        return $this;
    }

    public function removeTypeDiplome(TypeDiplome $typeDiplome): self
    {
        $this->typeDiplomes->removeElement($typeDiplome);

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
            $elementConstitutif->setTypeEc($this);
        }

        return $this;
    }

    public function removeElementConstitutif(ElementConstitutif $elementConstitutif): self
    {
        if ($this->elementConstitutifs->removeElement($elementConstitutif)) {
            // set the owning side to null (unless already changed)
            if ($elementConstitutif->getTypeEc() === $this) {
                $elementConstitutif->setTypeEc(null);
            }
        }

        return $this;
    }

    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    public function setFormation(?Formation $formation): self
    {
        $this->formation = $formation;

        return $this;
    }
}
