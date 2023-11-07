<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/Parcours.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Entity;

use App\Classes\verif\ParcoursValide;
use App\DTO\Remplissage;
use App\Entity\Traits\LifeCycleTrait;
use App\Enums\ModaliteEnseignementEnum;
use App\Enums\RegimeInscriptionEnum;
use App\Repository\ParcoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: ParcoursRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Parcours
{
    use LifeCycleTrait;

    public const PARCOURS_DEFAUT = 'Parcours par défaut';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(targetEntity: Formation::class, inversedBy: 'parcours', fetch: 'EAGER')]
    private ?Formation $formation;

    #[ORM\OneToMany(mappedBy: 'parcours', targetEntity: BlocCompetence::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['ordre' => 'ASC'])]
    private Collection $blocCompetences;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contenuFormation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $objectifsParcours = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resultatsAttendus = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rythmeFormationTexte = null;

    #[ORM\ManyToOne]
    private ?Ville $ville = null;

    #[ORM\Column(nullable: true)]
    private ?bool $hasStage = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $stageText = null;

    #[ORM\Column(nullable: true)]
    private ?float $nbHeuresStages;

    #[ORM\Column(nullable: true)]
    private ?bool $hasProjet = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $projetText = null;

    #[ORM\Column(nullable: true)]
    private ?float $nbHeuresProjet;

    #[ORM\Column(nullable: true)]
    private ?bool $hasMemoire = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $memoireText = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, enumType: ModaliteEnseignementEnum::class)]
    private ?ModaliteEnseignementEnum $modalitesEnseignement = ModaliteEnseignementEnum::NON_DEFINI;

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
    #[ORM\OrderBy(['ordre' => 'ASC'])]
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
    private ?bool $hasSituationPro = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $situationProText = null;

    #[ORM\Column(nullable: true)]
    private ?float $nbHeuresSituationPro = null;

    #[ORM\ManyToOne]
    private ?Ville $localisation = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $sigle = null;

    #[Ignore]
    #[ORM\Column]
    private ?array $etatSteps = [];

    #[ORM\Column(nullable: true)]
    private ?array $etatParcours = [];

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'parcours', targetEntity: FicheMatiere::class)]
    private Collection $ficheMatieres;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'parcours', targetEntity: FicheMatiereMutualisable::class)]
    private Collection $ficheMatiereParcours;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'parcours', targetEntity: SemestreMutualisable::class)]
    private Collection $semestreMutualisables;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'parcours', targetEntity: UeMutualisable::class)]
    private Collection $ueMutualisables;

    #[ORM\ManyToOne(inversedBy: 'coParcours')]
    private ?User $coResponsable = null;

    #[Ignore]
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'parcoursEnfants')]
    private ?self $parcoursParent = null;

    #[ORM\OneToMany(mappedBy: 'parcoursParent', targetEntity: self::class)]
    private Collection $parcoursEnfants;

    #[ORM\OneToMany(mappedBy: 'parcours', targetEntity: HistoriqueParcours::class)]
    private Collection $historiqueParcours;

    #[ORM\Column(nullable: true)]
    private ?array $remplissage = [];

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'parcours', targetEntity: ElementConstitutif::class)]
    private Collection $elementConstitutifs;

    #[ORM\OneToMany(mappedBy: 'parcours', targetEntity: CommentaireParcours::class)]
    private Collection $commentaires;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'parcours', targetEntity: ParcoursVersioning::class)]
    private Collection $parcoursVersionings;

    public function __construct(Formation $formation)
    {
        $this->formation = $formation;
        $this->blocCompetences = new ArrayCollection();
        $this->semestreParcours = new ArrayCollection();

        for ($i = 1; $i <= 6; $i++) {
            $this->etatSteps[$i] = false;
        }
        $this->ficheMatieres = new ArrayCollection();
        $this->ficheMatiereParcours = new ArrayCollection();
        $this->semestreMutualisables = new ArrayCollection();
        $this->ueMutualisables = new ArrayCollection();
        $this->parcoursEnfants = new ArrayCollection();
        $this->historiqueParcours = new ArrayCollection();
        $this->elementConstitutifs = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->parcoursVersionings = new ArrayCollection();
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

    public function setHasStage(?bool $hasStage): self
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

    public function setHasProjet(?bool $hasProjet): self
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

    public function setHasMemoire(?bool $hasMemoire): self
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

    public function remplissageBrut(): Remplissage
    {
        $verification = new ParcoursValide($this, $this->getFormation()?->getTypeDiplome());
        $verification->valideParcours();

        return $verification->calcul();
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
        if (count($this->regimeInscription) === 0) {
            if (count($this->getFormation()?->getRegimeInscription()) !== 0) {
                return $this->getFormation()?->getRegimeInscription();
            }
        }

        $t = [];
        foreach ($this->regimeInscription as $value) {
            if ($value instanceof RegimeInscriptionEnum) {
                $t[] = $value;
            } else {
                $t[] = RegimeInscriptionEnum::from($value);
            }
        }

        return $t;
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

    public function getObjectifsParcours(): ?string
    {
        return $this->objectifsParcours;
    }

    public function setObjectifsParcours(?string $objectifsParcours): self
    {
        $this->objectifsParcours = $objectifsParcours;

        return $this;
    }

    public function isHasSituationPro(): ?bool
    {
        return $this->hasSituationPro;
    }

    public function setHasSituationPro(bool $hasSituationPro): self
    {
        $this->hasSituationPro = $hasSituationPro;

        return $this;
    }

    public function getSituationProText(): ?string
    {
        return $this->situationProText;
    }

    public function setSituationProText(?string $situationProText): self
    {
        $this->situationProText = $situationProText;

        return $this;
    }

    public function getEtatParcours(): array
    {
        return $this->etatParcours ?? [];
    }

    public function setEtatParcours(?array $etatParcours): self
    {
        $this->etatParcours = $etatParcours;

        return $this;
    }

    public function isParcoursDefaut(): bool
    {
        return $this->libelle === self::PARCOURS_DEFAUT;
    }

    public function getTypeDiplome(): ?TypeDiplome
    {
        return $this->getFormation()?->getTypeDiplome();
    }

    /**
     * @return Collection<int, FicheMatiere>
     */
    public function getFicheMatieres(): Collection
    {
        return $this->ficheMatieres;
    }

    public function addFicheMatiere(FicheMatiere $ficheMatiere): self
    {
        if (!$this->ficheMatieres->contains($ficheMatiere)) {
            $this->ficheMatieres->add($ficheMatiere);
            $ficheMatiere->setParcours($this);
        }

        return $this;
    }

    public function removeFicheMatiere(FicheMatiere $ficheMatiere): self
    {
        if ($this->ficheMatieres->removeElement($ficheMatiere)) {
            // set the owning side to null (unless already changed)
            if ($ficheMatiere->getParcours() === $this) {
                $ficheMatiere->setParcours(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FicheMatiereMutualisable>
     */
    public function getFicheMatiereParcours(): Collection
    {
        return $this->ficheMatiereParcours;
    }

    public function addFicheMatiereParcour(FicheMatiereMutualisable $ficheMatiereParcour): self
    {
        if (!$this->ficheMatiereParcours->contains($ficheMatiereParcour)) {
            $this->ficheMatiereParcours->add($ficheMatiereParcour);
            $ficheMatiereParcour->setParcours($this);
        }

        return $this;
    }

    public function removeFicheMatiereParcour(FicheMatiereMutualisable $ficheMatiereParcour): self
    {
        if ($this->ficheMatiereParcours->removeElement($ficheMatiereParcour)) {
            // set the owning side to null (unless already changed)
            if ($ficheMatiereParcour->getParcours() === $this) {
                $ficheMatiereParcour->setParcours(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SemestreMutualisable>
     */
    public function getSemestreMutualisables(): Collection
    {
        return $this->semestreMutualisables;
    }

    public function addSemestreMutualisable(SemestreMutualisable $semestreMutualisable): self
    {
        if (!$this->semestreMutualisables->contains($semestreMutualisable)) {
            $this->semestreMutualisables->add($semestreMutualisable);
            $semestreMutualisable->setParcours($this);
        }

        return $this;
    }

    public function removeSemestreMutualisable(SemestreMutualisable $semestreMutualisable): self
    {
        if ($this->semestreMutualisables->removeElement($semestreMutualisable)) {
            // set the owning side to null (unless already changed)
            if ($semestreMutualisable->getParcours() === $this) {
                $semestreMutualisable->setParcours(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UeMutualisable>
     */
    public function getUeMutualisables(): Collection
    {
        return $this->ueMutualisables;
    }

    public function addUeMutualisable(UeMutualisable $ueMutualisable): self
    {
        if (!$this->ueMutualisables->contains($ueMutualisable)) {
            $this->ueMutualisables->add($ueMutualisable);
            $ueMutualisable->setParcours($this);
        }

        return $this;
    }

    public function removeUeMutualisable(UeMutualisable $ueMutualisable): self
    {
        if ($this->ueMutualisables->removeElement($ueMutualisable)) {
            // set the owning side to null (unless already changed)
            if ($ueMutualisable->getParcours() === $this) {
                $ueMutualisable->setParcours(null);
            }
        }

        return $this;
    }

    public function getCoResponsable(): ?User
    {
        return $this->coResponsable;
    }

    public function setCoResponsable(?User $coResponsable): self
    {
        $this->coResponsable = $coResponsable;

        return $this;
    }

    public function getParcoursParent(): ?self
    {
        return $this->parcoursParent;
    }

    public function setParcoursParent(?self $parcoursParent): self
    {
        $this->parcoursParent = $parcoursParent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getParcoursEnfants(): Collection
    {
        return $this->parcoursEnfants;
    }

    public function addParcoursEnfant(self $parcoursEnfant): self
    {
        if (!$this->parcoursEnfants->contains($parcoursEnfant)) {
            $this->parcoursEnfants->add($parcoursEnfant);
            $parcoursEnfant->setParcoursParent($this);
        }

        return $this;
    }

    public function removeParcoursEnfant(self $parcoursEnfant): self
    {
        if ($this->parcoursEnfants->removeElement($parcoursEnfant)) {
            // set the owning side to null (unless already changed)
            if ($parcoursEnfant->getParcoursParent() === $this) {
                $parcoursEnfant->setParcoursParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HistoriqueParcours>
     */
    public function getHistoriqueParcours(): Collection
    {
        return $this->historiqueParcours;
    }

    public function addHistoriqueParcour(HistoriqueParcours $historiqueParcour): static
    {
        if (!$this->historiqueParcours->contains($historiqueParcour)) {
            $this->historiqueParcours->add($historiqueParcour);
            $historiqueParcour->setParcours($this);
        }

        return $this;
    }

    public function removeHistoriqueParcour(HistoriqueParcours $historiqueParcour): static
    {
        if ($this->historiqueParcours->removeElement($historiqueParcour)) {
            // set the owning side to null (unless already changed)
            if ($historiqueParcour->getParcours() === $this) {
                $historiqueParcour->setParcours(null);
            }
        }

        return $this;
    }

    public function getValide()
    {
        return $this->getEtatParcours()['valide'] ?? false;
    }

    public function getRemplissage(): Remplissage
    {
        $remplissage = new Remplissage();

        if ($this->remplissage !== null &&
            count($this->remplissage) > 0
            && array_key_exists('score', $this->remplissage)
            && array_key_exists('total', $this->remplissage)) {
            $remplissage->setScore($this->remplissage['score']);
            $remplissage->setTotal($this->remplissage['total']);
        }


        return $remplissage;
    }

    public function setRemplissage(?Remplissage $remplissage = null): static
    {

        if (null === $remplissage) {
            $remplissage = $this->remplissageBrut();
        }

        $this->remplissage = [
            'score' => $remplissage->score,
            'total' => $remplissage->total,
        ];

        return $this;
    }

    #[ORM\PreFlush]
    public function updateRemplissage(PreFlushEventArgs $args): void
    {
        $remplissage = $this->remplissageBrut();
        $this->setRemplissage($remplissage);
    }

    /**
     * @return Collection<int, ElementConstitutif>
     */
    public function getElementConstitutifs(): Collection
    {
        return $this->elementConstitutifs;
    }

    public function addElementConstitutif(ElementConstitutif $elementConstitutif): static
    {
        if (!$this->elementConstitutifs->contains($elementConstitutif)) {
            $this->elementConstitutifs->add($elementConstitutif);
            $elementConstitutif->setParcours($this);
        }

        return $this;
    }

    public function removeElementConstitutif(ElementConstitutif $elementConstitutif): static
    {
        if ($this->elementConstitutifs->removeElement($elementConstitutif)) {
            // set the owning side to null (unless already changed)
            if ($elementConstitutif->getParcours() === $this) {
                $elementConstitutif->setParcours(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommentaireParcours>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(CommentaireParcours $commentaireParcour): static
    {
        if (!$this->commentaires->contains($commentaireParcour)) {
            $this->commentaires->add($commentaireParcour);
            $commentaireParcour->setParcours($this);
        }

        return $this;
    }

    public function removeCommentaire(CommentaireParcours $commentaireParcour): static
    {
        if ($this->commentaires->removeElement($commentaireParcour)) {
            // set the owning side to null (unless already changed)
            if ($commentaireParcour->getParcours() === $this) {
                $commentaireParcour->setParcours(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ParcoursVersioning>
     */
    public function getParcoursVersionings(): Collection
    {
        return $this->parcoursVersionings;
    }

    public function addParcoursVersioning(ParcoursVersioning $parcoursVersioning): static
    {
        if (!$this->parcoursVersionings->contains($parcoursVersioning)) {
            $this->parcoursVersionings->add($parcoursVersioning);
            $parcoursVersioning->setParcours($this);
        }

        return $this;
    }

    public function removeParcoursVersioning(ParcoursVersioning $parcoursVersioning): static
    {
        if ($this->parcoursVersionings->removeElement($parcoursVersioning)) {
            // set the owning side to null (unless already changed)
            if ($parcoursVersioning->getParcours() === $this) {
                $parcoursVersioning->setParcours(null);
            }
        }

        return $this;
    }
}
