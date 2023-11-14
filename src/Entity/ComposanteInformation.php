<?php

namespace App\Entity;

use App\Repository\ComposanteInformationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ComposanteInformationRepository::class)]
class ComposanteInformation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $restauration = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $hebergement = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $transport = null;

    #[ORM\OneToOne(mappedBy: 'composanteInformation', cascade: ['persist', 'remove'])]
    private ?Composante $composante = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRestauration(): ?string
    {
        return $this->restauration;
    }

    public function setRestauration(?string $restauration): static
    {
        $this->restauration = $restauration;

        return $this;
    }

    public function getHebergement(): ?string
    {
        return $this->hebergement;
    }

    public function setHebergement(?string $hebergement): static
    {
        $this->hebergement = $hebergement;

        return $this;
    }

    public function getTransport(): ?string
    {
        return $this->transport;
    }

    public function setTransport(?string $transport): static
    {
        $this->transport = $transport;

        return $this;
    }

    public function getComposante(): ?Composante
    {
        return $this->composante;
    }

    public function setComposante(?Composante $composante): static
    {
        // unset the owning side of the relation if necessary
        if ($composante === null && $this->composante !== null) {
            $this->composante->setComposanteInformation(null);
        }

        // set the owning side of the relation if necessary
        if ($composante !== null && $composante->getComposanteInformation() !== $this) {
            $composante->setComposanteInformation($this);
        }

        $this->composante = $composante;

        return $this;
    }
}
