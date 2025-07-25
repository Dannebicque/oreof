<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/AnneeUniversitaire.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/02/2023 22:43
 */

namespace App\Entity;

use App\Repository\CampagneCollecteRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CampagneCollecteRepository::class)]
class CampagneCollecte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(length: 30)]
    private ?string $libelle = null;

    #[ORM\Column(length: 30)]
    private ?string $couleur = 'primary';

    #[Groups('parcours_json_versioning')]
    #[ORM\Column]
    private ?int $annee = null;

    #[ORM\Column]
    private ?bool $defaut = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateOuvertureDpe = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateClotureDpe = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateTransmissionSes = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateCfvu = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $datePublication = null;

    #[ORM\Column(length: 1)]
    private ?string $codeApogee = null; //todo: déplacer dans année ?

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'dpes')]
    private ?AnneeUniversitaire $annee_universitaire = null;

    #[ORM\OneToMany(mappedBy: 'campagneCollecte', targetEntity: DpeParcours::class)]
    private Collection $dpeParcours;

    #[ORM\Column]
    private ?bool $mailDpeEnvoye = false;

    /**
     * @var Collection<int, ChangeRf>
     */
    #[ORM\OneToMany(mappedBy: 'campagneCollecte', targetEntity: ChangeRf::class)]
    private Collection $changeRves;

    /**
     * @var Collection<int, BlocCompetence>
     */
    #[ORM\OneToMany(mappedBy: 'campagneCollecte', targetEntity: BlocCompetence::class)]
    private Collection $blocCompetences;

    /**
     * @var Collection<int, UserProfil>
     */
    #[ORM\OneToMany(mappedBy: 'campagneCollecte', targetEntity: UserProfil::class)]
    private Collection $userProfils;

    /**
     * @var Collection<int, ButCompetence>
     */
    #[ORM\OneToMany(mappedBy: 'campagneCollecte', targetEntity: ButCompetence::class)]
    private Collection $butCompetences;

    /**
     * @var Collection<int, DpeDemande>
     */
    #[ORM\OneToMany(mappedBy: 'campagneCollecte', targetEntity: DpeDemande::class)]
    private Collection $dpeDemandes;

    public function __construct()
    {
        $this->dpeParcours = new ArrayCollection();
        $this->changeRves = new ArrayCollection();
        $this->blocCompetences = new ArrayCollection();
        $this->userProfils = new ArrayCollection();
        $this->butCompetences = new ArrayCollection();
        $this->dpeDemandes = new ArrayCollection();
    }

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

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(int $annee): self
    {
        $this->annee = $annee;

        return $this;
    }

    public function isDefaut(): ?bool
    {
        return $this->defaut;
    }

    public function setDefaut(bool $defaut): self
    {
        $this->defaut = $defaut;

        return $this;
    }

    public function getDateTransmissionSes(): ?DateTimeInterface
    {
        return $this->dateTransmissionSes;
    }

    public function setDateTransmissionSes(?DateTimeInterface $dateTransmissionSes): self
    {
        $this->dateTransmissionSes = $dateTransmissionSes;

        return $this;
    }

    public function getDateCfvu(): ?DateTimeInterface
    {
        return $this->dateCfvu;
    }

    public function setDateCfvu(?DateTimeInterface $dateCfvu): self
    {
        $this->dateCfvu = $dateCfvu;

        return $this;
    }

    public function getDateOuvertureDpe(): ?DateTimeInterface
    {
        return $this->dateOuvertureDpe;
    }

    public function setDateOuvertureDpe(?DateTimeInterface $dateOuvertureDpe): self
    {
        $this->dateOuvertureDpe = $dateOuvertureDpe;

        return $this;
    }

    public function getDateClotureDpe(): ?DateTimeInterface
    {
        return $this->dateClotureDpe;
    }

    public function setDateClotureDpe(?DateTimeInterface $dateClotureDpe): self
    {
        $this->dateClotureDpe = $dateClotureDpe;

        return $this;
    }

    public function getDatePublication(): ?DateTimeInterface
    {
        return $this->datePublication;
    }

    public function setDatePublication(?DateTimeInterface $datePublication): self
    {
        $this->datePublication = $datePublication;

        return $this;
    }

    public function getCodeApogee(): ?string
    {
        return $this->codeApogee;
    }

    public function setCodeApogee(string $codeApogee): static
    {
        $this->codeApogee = $codeApogee;

        return $this;
    }

    public function getAnneeUniversitaire(): ?AnneeUniversitaire
    {
        return $this->annee_universitaire;
    }

    public function setAnneeUniversitaire(?AnneeUniversitaire $annee_universitaire): static
    {
        $this->annee_universitaire = $annee_universitaire;

        return $this;
    }

    /**
     * @return Collection<int, DpeParcours>
     */
    public function getDpeParcours(): Collection
    {
        return $this->dpeParcours;
    }

    public function addDpeParcour(DpeParcours $dpeParcour): static
    {
        if (!$this->dpeParcours->contains($dpeParcour)) {
            $this->dpeParcours->add($dpeParcour);
            $dpeParcour->setCampagneCollecte($this);
        }

        return $this;
    }

    public function removeDpeParcour(DpeParcours $dpeParcour): static
    {
        if ($this->dpeParcours->removeElement($dpeParcour)) {
            // set the owning side to null (unless already changed)
            if ($dpeParcour->getCampagneCollecte() === $this) {
                $dpeParcour->setCampagneCollecte(null);
            }
        }

        return $this;
    }

    public function isMailDpeEnvoye(): ?bool
    {
        return $this->mailDpeEnvoye ?? false;
    }

    public function setMailDpeEnvoye(bool $mailDpeEnvoye): static
    {
        $this->mailDpeEnvoye = $mailDpeEnvoye;

        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur = 'primary'): static
    {
        $this->couleur = $couleur;

        return $this;
    }

    /**
     * @return Collection<int, ChangeRf>
     */
    public function getChangeRves(): Collection
    {
        return $this->changeRves;
    }

    public function addChangeRf(ChangeRf $changeRf): static
    {
        if (!$this->changeRves->contains($changeRf)) {
            $this->changeRves->add($changeRf);
            $changeRf->setCampagneCollecte($this);
        }

        return $this;
    }

    public function removeChangeRf(ChangeRf $changeRf): static
    {
        if ($this->changeRves->removeElement($changeRf)) {
            // set the owning side to null (unless already changed)
            if ($changeRf->getCampagneCollecte() === $this) {
                $changeRf->setCampagneCollecte(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BlocCompetence>
     */
    public function getBlocCompetences(): Collection
    {
        return $this->blocCompetences;
    }

    public function addBlocCompetence(BlocCompetence $blocCompetence): static
    {
        if (!$this->blocCompetences->contains($blocCompetence)) {
            $this->blocCompetences->add($blocCompetence);
            $blocCompetence->setCampagneCollecte($this);
        }

        return $this;
    }

    public function removeBlocCompetence(BlocCompetence $blocCompetence): static
    {
        if ($this->blocCompetences->removeElement($blocCompetence)) {
            // set the owning side to null (unless already changed)
            if ($blocCompetence->getCampagneCollecte() === $this) {
                $blocCompetence->setCampagneCollecte(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserProfil>
     */
    public function getUserProfils(): Collection
    {
        return $this->userProfils;
    }

    public function addUserProfil(UserProfil $userProfil): static
    {
        if (!$this->userProfils->contains($userProfil)) {
            $this->userProfils->add($userProfil);
            $userProfil->setCampagneCollecte($this);
        }

        return $this;
    }

    public function removeUserProfil(UserProfil $userProfil): static
    {
        if ($this->userProfils->removeElement($userProfil)) {
            // set the owning side to null (unless already changed)
            if ($userProfil->getCampagneCollecte() === $this) {
                $userProfil->setCampagneCollecte(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ButCompetence>
     */
    public function getButCompetences(): Collection
    {
        return $this->butCompetences;
    }

    public function addButCompetence(ButCompetence $butCompetence): static
    {
        if (!$this->butCompetences->contains($butCompetence)) {
            $this->butCompetences->add($butCompetence);
            $butCompetence->setCampagneCollecte($this);
        }

        return $this;
    }

    public function removeButCompetence(ButCompetence $butCompetence): static
    {
        if ($this->butCompetences->removeElement($butCompetence)) {
            // set the owning side to null (unless already changed)
            if ($butCompetence->getCampagneCollecte() === $this) {
                $butCompetence->setCampagneCollecte(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DpeDemande>
     */
    public function getDpeDemandes(): Collection
    {
        return $this->dpeDemandes;
    }

    public function addDpeDemande(DpeDemande $dpeDemande): static
    {
        if (!$this->dpeDemandes->contains($dpeDemande)) {
            $this->dpeDemandes->add($dpeDemande);
            $dpeDemande->setCampagneCollecte($this);
        }

        return $this;
    }

    public function removeDpeDemande(DpeDemande $dpeDemande): static
    {
        if ($this->dpeDemandes->removeElement($dpeDemande)) {
            // set the owning side to null (unless already changed)
            if ($dpeDemande->getCampagneCollecte() === $this) {
                $dpeDemande->setCampagneCollecte(null);
            }
        }

        return $this;
    }
}
