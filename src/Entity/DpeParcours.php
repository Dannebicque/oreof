<?php

namespace App\Entity;

use App\Enums\TypeModificationDpeEnum;
use App\Repository\DpeParcoursRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DpeParcoursRepository::class)]
class DpeParcours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'dpeParcours')]
    private ?CampagneCollecte $campagneCollecte = null;

    #[ORM\ManyToOne(inversedBy: 'dpeParcours')]
    private ?Parcours $parcours = null;

    #[ORM\Column]
    private array $etatValidation = []; //le workflow

    #[ORM\Column(length: 10)]
    private ?string $version = null;

    #[ORM\Column(length: 255, enumType: TypeModificationDpeEnum::class, nullable: true)]
    private ?TypeModificationDpeEnum $etatReconduction = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created = null;

    #[ORM\ManyToOne(inversedBy: 'dpeParcours')]
    private ?Formation $formation = null;


    public function __construct()
    {
        $this->created = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getParcours(): ?Parcours
    {
        return $this->parcours;
    }

    public function setParcours(?Parcours $parcours): static
    {
        $this->parcours = $parcours;

        return $this;
    }

    public function getEtatValidation(): array
    {
        return $this->etatValidation ?? [];
    }

    public function setEtatValidation(array $etatValidation): static
    {
        $this->etatValidation = $etatValidation;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getEtatReconduction(): ?TypeModificationDpeEnum
    {
        return $this->etatReconduction;
    }

    public function setEtatReconduction(?TypeModificationDpeEnum $etatReconduction): static
    {
        $this->etatReconduction = $etatReconduction;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): static
    {
        $this->created = $created;

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

    public function withCfvu() : bool
    {
        return $this->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_MCCC ||
        $this->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE;
    }

    public function updateMinorVersion(): void
    {
        if ($this->getVersion() === null) {
            $this->setVersion('1.0');
        } else {
            $version = explode('.', $this->getVersion());

            $version[1] = (int)$version[1] + 1;
            $this->setVersion($version[0].'.'.$version[1]);
        }
    }

    public function isPubliable(): bool
    {
        // vérifier si etatDpe = publiable et date OK
        //todo: sauvegarder date publication dans DpeParcours ou récupérer depuis l'historique
        return array_key_exists('valide_a_publier', $this->getEtatValidation());
    }

    public function isReouvert(): bool
    {
        return $this->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_TEXTE ||
            $this->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE ||
            $this->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_MCCC;
    }
}
