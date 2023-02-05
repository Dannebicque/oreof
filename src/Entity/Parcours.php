<?php

namespace App\Entity;

use App\Entity\Traits\LifeCycleTrait;
use App\Enums\ModaliteEnseignementEnum;
use App\Repository\ParcoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParcoursRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Parcours
{
    use LifeCycleTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(targetEntity: Formation::class, inversedBy: 'parcours')]
    private ?Formation $formation = null;

    #[ORM\OneToMany(mappedBy: 'parcours', targetEntity: BlocCompetence::class)]
    private Collection $blocCompetences;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contenuFormation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resultatsAttendus = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rythmeFormationTexte = null;

    #[ORM\ManyToOne]
    private ?Ville $ville = null;

    #[ORM\Column]
    private ?bool $hasStage = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $stageText = null;

    #[ORM\Column(nullable: true)]
    private ?float $nbHeuresStages = 0;

    #[ORM\Column]
    private ?bool $hasProjet = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $projetText = null;

    #[ORM\Column]
    private ?float $nbHeuresProjet = 0;

    #[ORM\Column]
    private ?bool $hasMemoire = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $memoireText = null;

    #[ORM\Column(type: Types::INTEGER,  nullable: true, enumType: ModaliteEnseignementEnum::class)]
    private ?ModaliteEnseignementEnum $modalitesEnseignement = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $prerequis = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $poursuitesEtudes = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $debouches = null;

    #[ORM\Column(nullable: true)]
    private ?array $codesRome = [];

    #[ORM\ManyToOne()]
    private ?RythmeFormation $rythmeFormation = null;

    #[ORM\OneToMany(mappedBy: 'parcours', targetEntity: SemestreParcours::class)]
    private Collection $semestreParcours;

    #[ORM\ManyToOne]
    private ?Composante $composanteInscription = null;

    #[ORM\Column(nullable: true)]
    private ?array $regimeInscription = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $modalitesAlternance = null;

    #[ORM\ManyToOne]
    private ?User $respParcours = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $coordSecretariat = null;

    #[ORM\Column(nullable: true)]
    private ?float $nbHeuresSituationPro = null;

    #[ORM\ManyToOne]
    private ?Ville $localisation = null;

    public function __construct(Formation $formation)
    {
        $this->formation = $formation;
        $this->semestres = new ArrayCollection();
        $this->blocCompetences = new ArrayCollection();
        $this->semestreParcours = new ArrayCollection();
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

    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    public function setFormation(?Formation $formation): self
    {
        $this->formation = $formation;

        return $this;
    }

    /**
     * @return Collection<int, BlocCompetence>
     */
    public function getBlocCompetences(): Collection
    {
        return $this->blocCompetences;
    }

    public function addBlocCompetence(BlocCompetence $blocCompetence): self
    {
        if (!$this->blocCompetences->contains($blocCompetence)) {
            $this->blocCompetences->add($blocCompetence);
            $blocCompetence->setParcours($this);
        }

        return $this;
    }

    public function removeBlocCompetence(BlocCompetence $blocCompetence): self
    {
        if ($this->blocCompetences->removeElement($blocCompetence)) {
            // set the owning side to null (unless already changed)
            if ($blocCompetence->getParcours() === $this) {
                $blocCompetence->setParcours(null);
            }
        }

        return $this;
    }

    public function getVille(): ?Ville
    {
        return $this->ville;
    }

    public function setVille(?Ville $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getContenuFormation(): ?string
    {
        return $this->contenuFormation;
    }

    public function setContenuFormation(?string $contenuFormation): self
    {
        $this->contenuFormation = $contenuFormation;

        return $this;
    }

    public function getResultatsAttendus(): ?string
    {
        return $this->resultatsAttendus;
    }

    public function setResultatsAttendus(?string $resultatsAttendus): self
    {
        $this->resultatsAttendus = $resultatsAttendus;

        return $this;
    }

    public function getRythmeFormationTexte(): ?string
    {
        return $this->rythmeFormationTexte;
    }

    public function setRythmeFormationTexte(?string $rythmeFormationTexte): self
    {
        $this->rythmeFormationTexte = $rythmeFormationTexte;

        return $this;
    }

    public function isHasStage(): ?bool
    {
        return $this->hasStage;
    }

    public function setHasStage(bool $hasStage): self
    {
        $this->hasStage = $hasStage;

        return $this;
    }

    public function getStageText(): ?string
    {
        return $this->stageText;
    }

    public function setStageText(?string $stageText): self
    {
        $this->stageText = $stageText;

        return $this;
    }

    public function getNbHeuresStages(): ?float
    {
        return $this->nbHeuresStages;
    }

    public function setNbHeuresStages(?float $nbHeuresStages): self
    {
        $this->nbHeuresStages = $nbHeuresStages;

        return $this;
    }

    public function isHasProjet(): ?bool
    {
        return $this->hasProjet;
    }

    public function setHasProjet(bool $hasProjet): self
    {
        $this->hasProjet = $hasProjet;

        return $this;
    }

    public function getProjetText(): ?string
    {
        return $this->projetText;
    }

    public function setProjetText(?string $projetText): self
    {
        $this->projetText = $projetText;

        return $this;
    }

    public function getNbHeuresProjet(): ?float
    {
        return $this->nbHeuresProjet;
    }

    public function setNbHeuresProjet(float $nbHeuresProjet): self
    {
        $this->nbHeuresProjet = $nbHeuresProjet;

        return $this;
    }

    public function isHasMemoire(): ?bool
    {
        return $this->hasMemoire;
    }

    public function setHasMemoire(bool $hasMemoire): self
    {
        $this->hasMemoire = $hasMemoire;

        return $this;
    }

    public function getMemoireText(): ?string
    {
        return $this->memoireText;
    }

    public function setMemoireText(?string $memoireText): self
    {
        $this->memoireText = $memoireText;

        return $this;
    }

    public function getModalitesEnseignement(): ?ModaliteEnseignementEnum
    {
        return $this->modalitesEnseignement;
    }

    public function setModalitesEnseignement(?ModaliteEnseignementEnum $modalitesEnseignement): self
    {
        $this->modalitesEnseignement = $modalitesEnseignement;

        return $this;
    }

    public function getPrerequis(): ?string
    {
        return $this->prerequis;
    }

    public function setPrerequis(?string $prerequis): self
    {
        $this->prerequis = $prerequis;

        return $this;
    }

    public function getPoursuitesEtudes(): ?string
    {
        return $this->poursuitesEtudes;
    }

    public function setPoursuitesEtudes(?string $poursuitesEtudes): self
    {
        $this->poursuitesEtudes = $poursuitesEtudes;

        return $this;
    }

    public function getDebouches(): ?string
    {
        return $this->debouches;
    }

    public function setDebouches(?string $debouches): self
    {
        $this->debouches = $debouches;

        return $this;
    }

    public function getCodesRome(): array
    {
        return $this->codesRome ?? [];
    }

    public function setCodesRome(?array $codesRome): self
    {
        $this->codesRome = $codesRome;

        return $this;
    }

    public function etat(): string
    {
        //todo: a gérer
        return 'En cours de rédaction';
    }

    public function remplissage(): float
    {
        //todo: a gérer
        return 20;
    }

    public function getRythmeFormation(): ?RythmeFormation
    {
        return $this->rythmeFormation;
    }

    public function setRythmeFormation(?RythmeFormation $rythmeFormation): self
    {
        $this->rythmeFormation = $rythmeFormation;

        return $this;
    }

    /**
     * @return Collection<int, SemestreParcours>
     */
    public function getSemestreParcours(): Collection
    {
        return $this->semestreParcours;
    }

    public function addSemestreParcour(SemestreParcours $semestreParcour): self
    {
        if (!$this->semestreParcours->contains($semestreParcour)) {
            $this->semestreParcours->add($semestreParcour);
            $semestreParcour->setParcours($this);
        }

        return $this;
    }

    public function removeSemestreParcour(SemestreParcours $semestreParcour): self
    {
        if ($this->semestreParcours->removeElement($semestreParcour)) {
            // set the owning side to null (unless already changed)
            if ($semestreParcour->getParcours() === $this) {
                $semestreParcour->setParcours(null);
            }
        }

        return $this;
    }

    public function getComposanteInscription(): ?Composante
    {
        return $this->composanteInscription;
    }

    public function setComposanteInscription(?Composante $composanteInscription): self
    {
        $this->composanteInscription = $composanteInscription;

        return $this;
    }

    public function getModalitesAlternance(): ?string
    {
        return $this->modalitesAlternance;
    }

    public function setModalitesAlternance(?string $modalitesAlternance): self
    {
        $this->modalitesAlternance = $modalitesAlternance;

        return $this;
    }

    public function getRegimeInscription(): array
    {
        return $this->regimeInscription;
    }

    public function setRegimeInscription(?array $regimeInscription): self
    {
        $this->regimeInscription = $regimeInscription;

        return $this;
    }

    public function getRespParcours(): ?User
    {
        return $this->respParcours;
    }

    public function setRespParcours(?User $respParcours): self
    {
        $this->respParcours = $respParcours;

        return $this;
    }

    public function getCoordSecretariat(): ?string
    {
        return $this->coordSecretariat;
    }

    public function setCoordSecretariat(?string $coordSecretariat): self
    {
        $this->coordSecretariat = $coordSecretariat;

        return $this;
    }

    public function getNbHeuresSituationPro(): ?float
    {
        return $this->nbHeuresSituationPro;
    }

    public function setNbHeuresSituationPro(?float $nbHeuresSituationPro): self
    {
        $this->nbHeuresSituationPro = $nbHeuresSituationPro;

        return $this;
    }

    public function getLocalisation(): ?Ville
    {
        return $this->localisation;
    }

    public function setLocalisation(?Ville $localisation): self
    {
        $this->localisation = $localisation;

        return $this;
    }
}
