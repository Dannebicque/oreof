<?php

namespace App\Entity;

use App\Enums\EtatDemandeChangeRfEnum;
use App\Enums\TypeRfEnum;
use App\Repository\ChangeRfRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChangeRfRepository::class)]
class ChangeRf
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'changeRves')]
    private ?Formation $formation = null;

    #[ORM\Column(length: 20, enumType: TypeRfEnum::class)]
    private ?TypeRfEnum $typeRf = null;

    #[ORM\Column(length: 20, enumType: EtatDemandeChangeRfEnum::class)]
    private ?EtatDemandeChangeRfEnum $etatDemande = null;

    #[ORM\ManyToOne]
    private ?User $nouveauResponsable = null;

    #[ORM\ManyToOne]
    private ?User $ancienResponsable = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDemande = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateValidationCfvu = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $fichier_pv = null;

    public function __construct()
    {
        $this->etatDemande = EtatDemandeChangeRfEnum::EN_ATTENTE;
        $this->typeRf = TypeRfEnum::RF;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEtatDemande(): ?EtatDemandeChangeRfEnum
    {
        return $this->etatDemande;
    }

    public function setEtatDemande(EtatDemandeChangeRfEnum $etatDemande): static
    {
        $this->etatDemande = $etatDemande;

        return $this;
    }

    public function getTypeRf(): ?TypeRfEnum
    {
        return $this->typeRf;
    }

    public function setTypeRf(TypeRfEnum $typeRf): static
    {
        $this->typeRf = $typeRf;

        return $this;
    }

    public function getNouveauResponsable(): ?User
    {
        return $this->nouveauResponsable;
    }

    public function setNouveauResponsable(?User $nouveauResponsable): static
    {
        $this->nouveauResponsable = $nouveauResponsable;

        return $this;
    }

    public function getAncienResponsable(): ?User
    {
        return $this->ancienResponsable;
    }

    public function setAncienResponsable(?User $ancienResponsable): static
    {
        $this->ancienResponsable = $ancienResponsable;

        return $this;
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

    public function getDateValidationCfvu(): ?\DateTimeInterface
    {
        return $this->dateValidationCfvu;
    }

    public function setDateValidationCfvu(?\DateTimeInterface $dateValidationCfvu): static
    {
        $this->dateValidationCfvu = $dateValidationCfvu;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getFichierPv(): ?string
    {
        return $this->fichier_pv;
    }

    public function setFichierPv(?string $fichier_pv): static
    {
        $this->fichier_pv = $fichier_pv;

        return $this;
    }
}
