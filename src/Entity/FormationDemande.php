<?php

namespace App\Entity;

use App\Repository\FormationDemandeRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormationDemandeRepository::class)]
class FormationDemande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?User $demandeur = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $dateDemande = null;

    #[ORM\Column]
    private ?bool $validationDpe = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateValidationDpe = null;

    #[ORM\ManyToOne]
    private ?Mention $mention = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mentionTexte = null;

    #[ORM\ManyToOne(inversedBy: 'formationDemandes')]
    private ?TypeDiplome $typeDiplome = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDemandeur(): ?User
    {
        return $this->demandeur;
    }

    public function setDemandeur(?User $demandeur): self
    {
        $this->demandeur = $demandeur;

        return $this;
    }

    public function getDateDemande(): ?DateTimeInterface
    {
        return $this->dateDemande;
    }

    public function setDateDemande(DateTimeInterface $dateDemande): self
    {
        $this->dateDemande = $dateDemande;

        return $this;
    }

    public function isValidationDpe(): ?bool
    {
        return $this->validationDpe;
    }

    public function setValidationDpe(bool $validationDpe): self
    {
        $this->validationDpe = $validationDpe;

        return $this;
    }

    public function getDateValidationDpe(): ?DateTimeInterface
    {
        return $this->dateValidationDpe;
    }

    public function setDateValidationDpe(?DateTimeInterface $dateValidationDpe): self
    {
        $this->dateValidationDpe = $dateValidationDpe;

        return $this;
    }

    public function getMention(): ?Mention
    {
        return $this->mention;
    }

    public function setMention(?Mention $mention): self
    {
        $this->mention = $mention;

        return $this;
    }

    public function getMentionTexte(): ?string
    {
        return $this->mentionTexte;
    }

    public function setMentionTexte(?string $mentionTexte): self
    {
        $this->mentionTexte = $mentionTexte;

        return $this;
    }

    public function getTypeDiplome(): ?TypeDiplome
    {
        return $this->typeDiplome;
    }

    public function setTypeDiplome(?TypeDiplome $typeDiplome): self
    {
        $this->typeDiplome = $typeDiplome;

        return $this;
    }
}
