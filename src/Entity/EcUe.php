<?php

namespace App\Entity;

use App\Repository\EcUeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EcUeRepository::class)]
class EcUe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'ecUes')]
    private ?ElementConstitutif $ec;

    #[ORM\ManyToOne(inversedBy: 'ecUes')]
    private ?Ue $ue;


    public function __construct(Ue $ue, ElementConstitutif $ec)
    {
        $this->ue = $ue;
        $this->ec = $ec;
    }
    public function getId(): ?int
    {
        return $this->id;
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

    public function getUe(): ?Ue
    {
        return $this->ue;
    }

    public function setUe(?Ue $ue): self
    {
        $this->ue = $ue;

        return $this;
    }
}
