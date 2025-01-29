<?php

namespace App\Entity;

use App\Enums\EtatDpeEnum;
use App\Enums\TypeModificationDpeEnum;
use App\Repository\DpeDemandeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DpeDemandeRepository::class)]
class DpeDemande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDemande = null;

    #[ORM\ManyToOne]
    private ?Parcours $parcours = null;

    #[ORM\ManyToOne]
    private ?Formation $formation = null;

    #[ORM\Column(length: 50, enumType: TypeModificationDpeEnum::class)]
    private ?TypeModificationDpeEnum $niveauModification = null;

    #[ORM\Column(length: 50, enumType: EtatDpeEnum::class)]
    private ?EtatDpeEnum $etatDemande = null;

    #[ORM\Column(length: 1)]
    private ?string $niveauDemande = 'P'; //ou F

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateReponseSes = null;

    #[ORM\Column(nullable: true)]
    private ?bool $reponseSes = false;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $argumentaireDemande = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $argumentaireSes = null;

    //constructeur pour initialiser la date de demande
    public function __construct()
    {
        $this->dateDemande = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->dateDemande;
    }

    public function setDateDemande(\DateTimeInterface $dateDemande): static
    {
        $this->dateDemande = $dateDemande;

        return $this;
    }

    public function getParcours(): ?Parcours
    {
        return $this->parcours;
    }

    public function setParcours(?Parcours $parcours): static
    {
        $this->parcours = $parcours;

        return $this;
    }

    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    public function setFormation(?Formation $formation): static
    {
        $this->formation = $formation;

        return $this;
    }

    public function getNiveauModification(): ?TypeModificationDpeEnum
    {
        return $this->niveauModification;
    }

    public function setNiveauModification(?TypeModificationDpeEnum $niveauModification): static
    {
        $this->niveauModification = $niveauModification;

        return $this;
    }

    public function getEtatDemande(): ?EtatDpeEnum
    {
        return $this->etatDemande;
    }

    public function setEtatDemande(EtatDpeEnum $etatDemande): static
    {
        $this->etatDemande = $etatDemande;

        return $this;
    }

    public function getNiveauDemande(): ?string
    {
        return $this->niveauDemande;
    }

    public function setNiveauDemande(string $niveauDemande): static
    {
        $this->niveauDemande = $niveauDemande;

        return $this;
    }

    public function getDateReponseSes(): ?\DateTimeInterface
    {
        return $this->dateReponseSes;
    }

    public function setDateReponseSes(?\DateTimeInterface $dateReponseSes): static
    {
        $this->dateReponseSes = $dateReponseSes;

        return $this;
    }

    public function isReponseSes(): ?bool
    {
        return $this->reponseSes;
    }

    public function setReponseSes(?bool $reponseSes): static
    {
        $this->reponseSes = $reponseSes;

        return $this;
    }

    public function getArgumentaireDemande(): ?string
    {
        return $this->argumentaireDemande;
    }

    public function setArgumentaireDemande(string $argumentaireDemande): static
    {
        $this->argumentaireDemande = $argumentaireDemande;

        return $this;
    }

    public function getArgumentaireSes(): ?string
    {
        return $this->argumentaireSes;
    }

    public function setArgumentaireSes(?string $argumentaireSes): static
    {
        $this->argumentaireSes = $argumentaireSes;

        return $this;
    }
}
