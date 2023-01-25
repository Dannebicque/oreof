<?php

namespace App\Entity;

use App\Repository\ComposanteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ComposanteRepository::class)]
class Composante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(inversedBy: 'composantes')]
    private ?User $directeur = null;

    #[ORM\ManyToOne]
    private ?User $responsableDpe = null;

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

    public function getDirecteur(): ?User
    {
        return $this->directeur;
    }

    public function setDirecteur(?User $directeur): self
    {
        $this->directeur = $directeur;

        return $this;
    }

    public function getResponsableDpe(): ?User
    {
        return $this->responsableDpe;
    }

    public function setResponsableDpe(?User $responsableDpe): self
    {
        $this->responsableDpe = $responsableDpe;

        return $this;
    }
}
