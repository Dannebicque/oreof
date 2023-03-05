<?php

namespace App\Entity;

use App\Entity\Traits\LifeCycleTrait;
use App\Enums\EtatDpeEnum;
use App\Enums\EtatRemplissageEnum;
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
    private ?Formation $formation;

    #[ORM\OneToMany(mappedBy: 'parcours', targetEntity: BlocCompetence::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['ordre' => 'ASC'])]
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

    #[ORM\ManyToOne]
    private ?RythmeFormation $rythmeFormation = null;

    #[ORM\OneToMany(mappedBy: 'parcours', targetEntity: SemestreParcours::class, cascade: ['persist', 'remove'])]
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

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $sigle = null;

    #[ORM\Column]
    private ?array $etatSteps = [];

    public function __construct(Formation $formation)
    {
        $this->formation = $formation;
        $this->blocCompetences = new ArrayCollection();
        $this->semestreParcours = new ArrayCollection();

        for ($i = 1; $i <= 8; $i++) {
            $this->etatSteps[$i] = false;
        }
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
        // set the owning side to null (unless already changed)
        if ($this->blocCompetences->removeElement($blocCompetence) && $blocCompetence->getParcours() === $this) {
            $blocCompetence->setParcours(null);
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

    public function etat(): array
    {
        //todo: a gérer wordlgow ?
        return [];
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
        // set the owning side to null (unless already changed)
        if ($this->semestreParcours->removeElement($semestreParcour) && $semestreParcour->getParcours() === $this) {
            $semestreParcour->setParcours(null);
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
        return $this->regimeInscription ?? [];
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

    public function getSigle(): ?string
    {
        return $this->sigle;
    }

    public function setSigle(?string $sigle): self
    {
        $this->sigle = $sigle;

        return $this;
    }

    public function onglets(): array
    {
        $onglets[0] = $this->getEtatOnglet0();
        $onglets[1] = $this->getEtatOnglet1();
        $onglets[2] = $this->getEtatOnglet2();
        $onglets[3] = $this->getEtatOnglet3();
        $onglets[4] = $this->getEtatOnglet4();
        $onglets[5] = $this->getEtatOnglet5();
        $onglets[6] = $this->getEtatOnglet6();
        $onglets[7] = $this->getEtatOnglet7();
        $onglets[8] = $this->getEtatOnglet8();

        return $onglets;
    }

    public function getEtatOnglet0(): EtatRemplissageEnum
    {
        //todo: ajouter les vérifs?
        return $this->getEtatStep(0) ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS;
    }

    public function getEtatOnglet1(): EtatRemplissageEnum
    {
        return $this->getContenuFormation() === null && $this->getResultatsAttendus() === null && $this->getRythmeFormation() === null ? EtatRemplissageEnum::VIDE : ($this->getEtatStep(1) && $this->getContenuFormation() !== null && $this->getResultatsAttendus() !== null && $this->getRythmeFormation() !== null ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS);
    }

    public function getEtatOnglet2(): EtatRemplissageEnum
    {
        //todo: ajouter les vérifs?
        return $this->getEtatStep(2) ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS;
    }

    public function getEtatOnglet3(): EtatRemplissageEnum
    {
        return $this->getBlocCompetences()->count() === 0 ? EtatRemplissageEnum::VIDE :
            ($this->getEtatStep(3) ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS);
    }

    public function getEtatOnglet4(): EtatRemplissageEnum
    {
        return $this->getSemestreParcours()->count() === 0 ? EtatRemplissageEnum::VIDE :
            ($this->getEtatStep(4) ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS);
    }

    public function getEtatOnglet5(): EtatRemplissageEnum
    {
        //todo: ajouter les vérifs?
        return $this->getEtatStep(5) ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS;
    }

    public function getEtatOnglet6(): EtatRemplissageEnum
    {
        //todo: ajouter les vérifs?
        return $this->getEtatStep(6) ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS;
    }

    public function getEtatOnglet7(): EtatRemplissageEnum
    {
        //todo: ajouter les vérifs?
        return $this->getEtatStep(7) ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS;
    }

    public function getEtatOnglet8(): EtatRemplissageEnum
    {
        //todo: ajouter les vérifs?
        return $this->getEtatStep(8) ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS;
    }

    public function getEtatStep(int $step): bool
    {
        if (array_key_exists($step, $this->getEtatSteps())) {
            return $this->getEtatSteps()[$step];
        }
        return false;
    }

    public function getEtatSteps(): array
    {
        return $this->etatSteps ?? [];
    }

    public function setEtatSteps(array $etatSteps): self
    {
        $this->etatSteps = $etatSteps;

        return $this;
    }
}
