<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/Mccc.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 20/02/2023 12:31
 */

namespace App\Entity;

use App\Repository\McccRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: McccRepository::class)]
class Mccc
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $libelle = null;

    #[ORM\Column]
    private ?int $numeroSession = null;

    #[ORM\Column]
    private ?bool $secondeChance = false;

    #[ORM\Column]
    private ?float $pourcentage = 0;

    #[ORM\Column]
    private ?int $nbEpreuves = 0;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $typeEpreuve = [];

    #[ORM\ManyToOne(inversedBy: 'mcccs')]
    private ?ElementConstitutif $ec = null;

    #[ORM\Column]
    private ?bool $controleContinu = null;

    #[ORM\Column]
    private ?bool $examenTerminal = null;

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

    public function getNumeroSession(): ?int
    {
        return $this->numeroSession;
    }

    public function setNumeroSession(int $numeroSession): self
    {
        $this->numeroSession = $numeroSession;

        return $this;
    }

    public function isSecondeChance(): ?bool
    {
        return $this->secondeChance;
    }

    public function setSecondeChance(bool $secondeChance): self
    {
        $this->secondeChance = $secondeChance;

        return $this;
    }

    public function getPourcentage(): ?float
    {
        return $this->pourcentage;
    }

    public function setPourcentage(float $pourcentage): self
    {
        $this->pourcentage = $pourcentage;

        return $this;
    }

    public function getNbEpreuves(): ?int
    {
        return $this->nbEpreuves;
    }

    public function setNbEpreuves(int $nbEpreuves): self
    {
        $this->nbEpreuves = $nbEpreuves;

        return $this;
    }

    public function getTypeEpreuve(): array
    {
        return $this->typeEpreuve ?? [];
    }

    public function setTypeEpreuve(?array $typeEpreuve): self
    {
        $this->typeEpreuve = $typeEpreuve;

        return $this;
    }

    public function getEc(): ?ElementConstitutif
    {
        return $this->ec;
    }

    public function setEc(?ElementConstitutif $ec): self
    {
        $this->ec = $ec;

        return $this;
    }

    public function isControleContinu(): ?bool
    {
        return $this->controleContinu;
    }

    public function setControleContinu(bool $controleContinu): self
    {
        $this->controleContinu = $controleContinu;

        return $this;
    }

    public function isExamenTerminal(): ?bool
    {
        return $this->examenTerminal;
    }

    public function setExamenTerminal(bool $examenTerminal): self
    {
        $this->examenTerminal = $examenTerminal;

        return $this;
    }
}
