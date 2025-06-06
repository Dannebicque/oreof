<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/TypeEpreuve.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 20/02/2023 14:23
 */

namespace App\Entity;

use App\Repository\TypeEpreuveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeEpreuveRepository::class)]
class TypeEpreuve
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $libelle = null;

    #[ORM\ManyToMany(targetEntity: TypeDiplome::class, inversedBy: 'typeEpreuves')]
    private Collection $typeDiplomes;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $sigle = null;

    #[ORM\Column(nullable: true)]
    private ?bool $hasDuree = false;

    #[ORM\Column(nullable: true)]
    private ?bool $hasJustification = null;

    public function __construct()
    {
        $this->typeDiplomes = new ArrayCollection();
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
     * @return Collection<int, TypeDiplome>
     */
    public function getTypeDiplomes(): Collection
    {
        return $this->typeDiplomes;
    }

    public function addTypeDiplome(TypeDiplome $typeDiplome): self
    {
        if (!$this->typeDiplomes->contains($typeDiplome)) {
            $this->typeDiplomes->add($typeDiplome);
        }

        return $this;
    }

    public function removeTypeDiplome(TypeDiplome $typeDiplome): self
    {
        $this->typeDiplomes->removeElement($typeDiplome);

        return $this;
    }

    public function getSigle(): ?string
    {
        return $this->sigle;
    }

    public function setSigle(?string $sigle): self
    {
        $this->sigle = $sigle;

        return $this;
    }

    public function display(): string
    {
        if ($this->sigle !== null) {
            return $this->getLibelle() . ' (' . $this->getSigle() . ')';
        }
        return $this->getLibelle();
    }

    public function isHasDuree(): ?bool
    {
        return $this->hasDuree;
    }

    public function setHasDuree(?bool $hasDuree): static
    {
        $this->hasDuree = $hasDuree;

        return $this;
    }

    public function hasJustification(): ?bool
    {
        return $this->hasJustification;
    }

    public function setHasJustification(?bool $hasJustification): static
    {
        $this->hasJustification = $hasJustification;

        return $this;
    }
}
