<?php

namespace App\Entity;

use App\Repository\UserCentreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserCentreRepository::class)]
class UserCentre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userCentres')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userCentres')]
    private ?Composante $composante = null;

    #[ORM\ManyToOne(inversedBy: 'userCentres')]
    private ?Formation $formation = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $droits = [];

    #[ORM\ManyToOne(inversedBy: 'userCentres')]
    private ?Etablissement $etablissement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getComposante(): ?Composante
    {
        return $this->composante;
    }

    public function setComposante(?Composante $composante): self
    {
        $this->composante = $composante;

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

    public function getDroits(): array
    {
        return $this->droits;
    }

    public function setDroits(array $droits): self
    {
        $this->droits = $droits;

        return $this;
    }

    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    public function setEtablissement(?Etablissement $etablissement): self
    {
        $this->etablissement = $etablissement;

        return $this;
    }
}
