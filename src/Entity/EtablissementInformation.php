<?php

namespace App\Entity;

use App\Repository\EtablissementInformationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EtablissementInformationRepository::class)]
class EtablissementInformation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $calendrier_universitaire = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $calendrier_inscription = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $informations_pratiques = null;

    #[ORM\OneToOne(inversedBy: 'etablissement_information', cascade: ['persist', 'remove'])]
    private ?Etablissement $etablissement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCalendrierUniversitaire(): ?string
    {
        return $this->calendrier_universitaire;
    }

    public function setCalendrierUniversitaire(?string $calendrier_universitaire): static
    {
        $this->calendrier_universitaire = $calendrier_universitaire;

        return $this;
    }

    public function getCalendrierInscription(): ?string
    {
        return $this->calendrier_inscription;
    }

    public function setCalendrierInscription(?string $calendrier_inscription): static
    {
        $this->calendrier_inscription = $calendrier_inscription;

        return $this;
    }

    public function getInformationsPratiques(): ?string
    {
        return $this->informations_pratiques;
    }

    public function setInformationsPratiques(?string $informations_pratiques): static
    {
        $this->informations_pratiques = $informations_pratiques;

        return $this;
    }

    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    public function setEtablissement(?Etablissement $etablissement): static
    {
        $this->etablissement = $etablissement;

        return $this;
    }
}
