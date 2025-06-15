<?php

namespace App\Entity;

use App\Classes\GetDpeParcours;
use App\Entity\Traits\LifeCycleTrait;
use App\Enums\BadgeEnumInterface;
use App\Enums\EtatDpeEnum;
use App\Enums\TypeModificationDpeEnum;
use App\Repository\DpeDemandeRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DpeDemandeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class DpeDemande
{
    use LifeCycleTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $dateDemande;

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

    #[ORM\Column(type: Types::TEXT)]
    private ?string $argumentaireDemande = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateCloture = null;

    #[ORM\ManyToOne(inversedBy: 'dpeDemandes')]
    private ?User $auteur = null;

    #[ORM\ManyToOne(inversedBy: 'dpeDemandes')]
    private ?CampagneCollecte $campagneCollecte = null;

    //constructeur pour initialiser la date de demande
    public function __construct()
    {
        $this->dateDemande = new DateTime();
    }

    public static function getListeNiveauDemande(): array
    {
        return [
            'F' => 'Parcours',
            'P' => 'Formation',
        ];
    }

    public static function getListeNiveauModification(): array
    {
        return [
            TypeModificationDpeEnum::ANNULATION_REOUVERTURE->value => TypeModificationDpeEnum::ANNULATION_REOUVERTURE->getLibelle(),
            TypeModificationDpeEnum::CREATION->value => TypeModificationDpeEnum::CREATION->getLibelle(),
            TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE->value => TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE->getLibelle(),
            TypeModificationDpeEnum::MODIFICATION_PARCOURS->value => TypeModificationDpeEnum::MODIFICATION_PARCOURS->getLibelle(),
            TypeModificationDpeEnum::MODIFICATION_TEXTE->value => TypeModificationDpeEnum::MODIFICATION_TEXTE->getLibelle(),
            TypeModificationDpeEnum::NON_OUVERTURE_SES->value => TypeModificationDpeEnum::NON_OUVERTURE_SES->getLibelle(),

        ];

    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDemande(): ?DateTimeInterface
    {
        return $this->dateDemande;
    }

    public function setDateDemande(DateTimeInterface $dateDemande): static
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

    public function getArgumentaireDemande(): ?string
    {
        return $this->argumentaireDemande;
    }

    public function setArgumentaireDemande(string $argumentaireDemande): static
    {
        $this->argumentaireDemande = $argumentaireDemande;

        return $this;
    }

    public function getDateCloture(): ?DateTimeInterface
    {
        return $this->dateCloture;
    }

    public function setDateCloture(?DateTimeInterface $dateCloture): static
    {
        $this->dateCloture = $dateCloture;

        return $this;
    }

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function setAuteur(?User $auteur): static
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function etatValidation(): ?BadgeEnumInterface
    {
        if ($this->niveauDemande === 'P') {
            $dpeParcours = GetDpeParcours::getFromParcours($this->parcours) ?? null;
            if ($dpeParcours !== null) {
                return EtatDpeEnum::tryFrom(array_keys($dpeParcours->getEtatValidation())[0]);
            }
        }

        if ($this->niveauDemande === 'F') {
            return $this->getEtatDemande();
        }

        return null;
    }

    public function getCampagneCollecte(): ?CampagneCollecte
    {
        return $this->campagneCollecte;
    }

    public function setCampagneCollecte(?CampagneCollecte $campagneCollecte): static
    {
        $this->campagneCollecte = $campagneCollecte;

        return $this;
    }
}
