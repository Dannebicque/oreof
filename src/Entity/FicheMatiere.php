<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/ElementConstitutif.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Entity;

use App\Entity\Traits\LifeCycleTrait;
use App\Enums\ModaliteEnseignementEnum;
use App\Repository\FicheMatiereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: FicheMatiereRepository::class)]
#[ORM\Index(name: 'sigle_fiche', columns: ['sigle'], flags: ['fulltext'])]
#[ORM\Index(name: 'slug_fiche', columns: ['slug'], flags: ['fulltext'])]
#[ORM\HasLifecycleCallbacks]
class FicheMatiere
{
    use LifeCycleTrait;

    public const TYPE_MATIERE_COURS = 'matiere';
    public const TYPE_MATIERE_SAE = 'sae';
    public const TYPE_MATIERE_RESSOURCE = 'ressource';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['fiche_matiere:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 250)]
    #[Groups(['fiche_matiere:read', 'parcours_json_versioning'])]
    private ?string $libelle = null;

    #[ORM\Column(length: 250, nullable: true)]
    #[Groups(['fiche_matiere:read'])]
    private ?string $libelleAnglais = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['fiche_matiere:read', 'parcours_json_versioning'])]
    private ?bool $enseignementMutualise = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['fiche_matiere:read'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['fiche_matiere:read'])]
    private ?string $objectifs = null;

    #[ORM\ManyToMany(targetEntity: Competence::class, inversedBy: 'ficheMatieres', cascade: ['persist'])]
    private Collection $competences;

    #[ORM\Column(length: 30, nullable: true, enumType: ModaliteEnseignementEnum::class)]
    #[Groups(['fiche_matiere:read'])]
    private ?ModaliteEnseignementEnum $modaliteEnseignement = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(nullable: true)]
    private ?bool $isCmPresentielMutualise = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(nullable: true)]
    private ?bool $isTdPresentielMutualise = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(nullable: true)]
    private ?bool $isTpPresentielMutualise = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(nullable: true)]
    private ?bool $isCmDistancielMutualise = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(nullable: true)]
    private ?bool $isTdDistancielMutualise = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(nullable: true)]
    private ?bool $isTpDistancielMutualise = null;

    #[ORM\ManyToOne]
    #[Groups(['fiche_matiere:read'])]
    private ?User $responsableFicheMatiere = null;

    #[ORM\ManyToMany(targetEntity: Langue::class, inversedBy: 'ficheMatieres', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'fiche_matiere_langue_dispense')]
    private Collection $langueDispense;

    #[ORM\ManyToMany(targetEntity: Langue::class, inversedBy: 'languesSupportsFicheMatieres', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'fiche_matiere_langue_support')]
    private Collection $langueSupport;

    #[ORM\Column]
    #[Groups(['fiche_matiere:read'])]
    private ?array $etatSteps = [];

    #[ORM\OneToMany(mappedBy: 'ficheMatiere', targetEntity: ElementConstitutif::class, cascade: ['persist', 'remove'])]
    private Collection $elementConstitutifs;

    #[ORM\ManyToOne(inversedBy: 'ficheMatieres')]
    private ?Parcours $parcours = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\OneToMany(mappedBy: 'ficheMatiere', targetEntity: FicheMatiereMutualisable::class)]
    private Collection $ficheMatiereParcours;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['fiche_matiere:read', 'parcours_json_versioning'])]
    private ?string $sigle = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $typeMatiere = 'matiere';

    #[ORM\OneToMany(mappedBy: 'ficheMatiere', targetEntity: HistoriqueFicheMatiere::class)]
    private Collection $historiqueFicheMatieres;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(length: 255, unique: true)]
    #[Gedmo\Slug(fields: ['libelle'])]
    private ?string $slug = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(nullable: true)]
    private ?float $volumeCmPresentiel;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(nullable: true)]
    private ?float $volumeTdPresentiel;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(nullable: true)]
    private ?float $volumeTpPresentiel;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(nullable: true)]
    private ?float $volumeTe = null;

    #[ORM\OneToMany(mappedBy: 'ficheMatiere', targetEntity: Mccc::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $mcccs;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $etatMccc = null;

    #[ORM\ManyToMany(targetEntity: ButApprentissageCritique::class, inversedBy: 'ficheMatieres')]
    private Collection $apprentissagesCritiques;

    #[ORM\Column(nullable: true)]
    private ?bool $sansNote = null;

    #[ORM\Column(nullable: true)]
    private ?bool $sansHeures = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(nullable: true)]
    private ?float $volumeCmDistanciel = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(nullable: true)]
    private ?float $volumeTdDistanciel = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(nullable: true)]
    private ?float $volumeTpDistanciel = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $typeMccc = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(nullable: true)]
    private ?bool $horsDiplome = null;

    #[ORM\ManyToOne(inversedBy: 'ficheMatieres')]
    private ?TypeDiplome $typeDiplome = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(nullable: true)]
    private ?bool $volumesHorairesImpose = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(nullable: true)]
    private ?bool $ectsImpose = null;

    #[ORM\Column(nullable: true)]
    private ?bool $mcccImpose = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(nullable: true)]
    private ?float $ects = null;

    #[ORM\ManyToMany(targetEntity: Composante::class, inversedBy: 'ficheMatieres')]
    private Collection $composante;

    #[ORM\OneToMany(mappedBy: 'ficheMatiere', targetEntity: CommentaireFicheMatiere::class)]
    private Collection $commentaires;

    #[ORM\Column(nullable: true)]
    private ?array $etatFiche = [];

    public function __construct()
    {
        $this->mcccs = new ArrayCollection();
        $this->competences = new ArrayCollection();
        $this->langueDispense = new ArrayCollection();
        $this->langueSupport = new ArrayCollection();

        for ($i = 1; $i <= 5; $i++) {
            $this->etatSteps[$i] = false;
        }
        $this->elementConstitutifs = new ArrayCollection();
        $this->ficheMatiereParcours = new ArrayCollection();
        $this->historiqueFicheMatieres = new ArrayCollection();
        $this->apprentissagesCritiques = new ArrayCollection();
        $this->composante = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
    }

    public function getEtatStep(int $step): bool
    {
        if (array_key_exists($step, $this->getEtatSteps())) {
            return $this->getEtatSteps()[$step];
        }
        return false;
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

    public function getLibelleAnglais(): ?string
    {
        return $this->libelleAnglais;
    }

    public function setLibelleAnglais(?string $libelleAnglais): self
    {
        $this->libelleAnglais = $libelleAnglais;

        return $this;
    }

    public function isEnseignementMutualise(): ?bool
    {
        return $this->enseignementMutualise;
    }

    public function setEnseignementMutualise(?bool $enseignementMutualise): self
    {
        $this->enseignementMutualise = $enseignementMutualise;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getObjectifs(): ?string
    {
        return $this->objectifs;
    }

    public function setObjectifs(?string $objectifs): self
    {
        $this->objectifs = $objectifs;

        return $this;
    }

    /**
     * @return Collection<int, Competence>
     */
    public function getCompetences(): Collection
    {
        return $this->competences;
    }

    public function addCompetence(Competence $competence): self
    {
        if (!$this->competences->contains($competence)) {
            $this->competences->add($competence);
        }

        return $this;
    }

    public function removeCompetence(Competence $competence): self
    {
        $this->competences->removeElement($competence);

        return $this;
    }

    public function getResponsableFicheMatiere(): ?User
    {
        return $this->responsableFicheMatiere;
    }

    public function setResponsableFicheMatiere(?User $responsableFicheMatiere): self
    {
        $this->responsableFicheMatiere = $responsableFicheMatiere;

        return $this;
    }

    public function remplissage(): float
    {
        //calcul un remplissage de l'entité en fonction des champs obligatoires
        $nbChampsRemplis = 0;

        if ($this->langueDispense->isEmpty() === false) {
            $nbChampsRemplis++;
        }

        if ($this->libelleAnglais !== null) {
            $nbChampsRemplis++;
        }

        if ($this->enseignementMutualise !== null) {
            $nbChampsRemplis++;
        }

        if ($this->description !== null) {
            $nbChampsRemplis++;
        }

        if ($this->objectifs !== null) {
            $nbChampsRemplis++;
        }

//        if ($this->competences->isEmpty() === false) {
//            $nbChampsRemplis++;
//        }
        //todo: ajouter MCCC et Apprentissages critiques pour le BUT

        $nbChampsObligatoires = 5;

        return round($nbChampsRemplis / $nbChampsObligatoires * 100, 2);
    }

    /**
     * @return Collection<int, Langue>
     */
    public function getLangueDispense(): Collection
    {
        return $this->langueDispense;
    }

    public function addLangueDispense(Langue $langueDispense): self
    {
        if (!$this->langueDispense->contains($langueDispense)) {
            $this->langueDispense->add($langueDispense);
        }

        return $this;
    }

    public function removeLangueDispense(Langue $langueDispense): self
    {
        $this->langueDispense->removeElement($langueDispense);

        return $this;
    }

    /**
     * @return Collection<int, Langue>
     */
    public function getLangueSupport(): Collection
    {
        return $this->langueSupport;
    }

    public function addLangueSupport(Langue $langueSupport): self
    {
        if (!$this->langueSupport->contains($langueSupport)) {
            $this->langueSupport->add($langueSupport);
        }

        return $this;
    }

    public function removeLangueSupport(Langue $langueSupport): self
    {
        $this->langueSupport->removeElement($langueSupport);

        return $this;
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

    /**
     * @return Collection<int, ElementConstitutif>
     */
    public function getElementConstitutifs(): Collection
    {
        return $this->elementConstitutifs;
    }

    public function addElementConstitutif(ElementConstitutif $elementConstitutif): self
    {
        if (!$this->elementConstitutifs->contains($elementConstitutif)) {
            $this->elementConstitutifs->add($elementConstitutif);
            $elementConstitutif->setFicheMatiere($this);
        }

        return $this;
    }

    public function removeElementConstitutif(ElementConstitutif $elementConstitutif): self
    {
        if ($this->elementConstitutifs->removeElement($elementConstitutif)) {
            // set the owning side to null (unless already changed)
            if ($elementConstitutif->getFicheMatiere() === $this) {
                $elementConstitutif->setFicheMatiere(null);
            }
        }

        return $this;
    }

    public function getParcours(): ?Parcours
    {
        return $this->parcours;
    }

    public function setParcours(?Parcours $parcours): self
    {
        $this->parcours = $parcours;

        return $this;
    }

    public function getModaliteEnseignement(): ?ModaliteEnseignementEnum
    {
        return $this->modaliteEnseignement;
    }

    public function setModaliteEnseignement(?ModaliteEnseignementEnum $modaliteEnseignement): self
    {
        $this->modaliteEnseignement = $modaliteEnseignement;

        return $this;
    }

    public function isIsCmPresentielMutualise(): ?bool
    {
        return $this->isCmPresentielMutualise;
    }

    public function setIsCmPresentielMutualise(?bool $isCmPresentielMutualise): self
    {
        $this->isCmPresentielMutualise = $isCmPresentielMutualise;

        return $this;
    }

    public function isIsTdPresentielMutualise(): ?bool
    {
        return $this->isTdPresentielMutualise;
    }

    public function setIsTdPresentielMutualise(?bool $isTdPresentielMutualise): self
    {
        $this->isTdPresentielMutualise = $isTdPresentielMutualise;

        return $this;
    }

    public function isIsTpPresentielMutualise(): ?bool
    {
        return $this->isTpPresentielMutualise;
    }

    public function setIsTpPresentielMutualise(?bool $isTpPresentielMutualise): self
    {
        $this->isTpPresentielMutualise = $isTpPresentielMutualise;

        return $this;
    }

    public function isIsCmDistancielMutualise(): ?bool
    {
        return $this->isCmDistancielMutualise;
    }

    public function setIsCmDistancielMutualise(?bool $isCmDistancielMutualise): self
    {
        $this->isCmDistancielMutualise = $isCmDistancielMutualise;

        return $this;
    }

    public function isIsTdDistancielMutualise(): ?bool
    {
        return $this->isTdDistancielMutualise;
    }

    public function setIsTdDistancielMutualise(?bool $isTdDistancielMutualise): self
    {
        $this->isTdDistancielMutualise = $isTdDistancielMutualise;

        return $this;
    }

    public function isIsTpDistancielMutualise(): ?bool
    {
        return $this->isTpDistancielMutualise;
    }

    public function setIsTpDistancielMutualise(?bool $isTpDistancielMutualise): self
    {
        $this->isTpDistancielMutualise = $isTpDistancielMutualise;

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
            $ficheMatiereParcour->setFicheMatiere($this);
        }

        return $this;
    }

    public function removeFicheMatiereParcour(FicheMatiereMutualisable $ficheMatiereParcour): self
    {
        if ($this->ficheMatiereParcours->removeElement($ficheMatiereParcour)) {
            // set the owning side to null (unless already changed)
            if ($ficheMatiereParcour->getFicheMatiere() === $this) {
                $ficheMatiereParcour->setFicheMatiere(null);
            }
        }

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

    #[Groups(['fiche_matiere:read'])]
    public function getDisplay(): ?string
    {
        $texte = $this->getLibelle();
        if ($this->sigle !== null && trim($this->sigle) !== '') {
            $texte .= ' | ' . $this->sigle;
        }

        if ($this->getFicheMatiereParcours()->count() > 0) {
            $texte .= ' (mutualisée)';
        }

        return $texte;
    }

    public function getTypeMatiere(): ?string
    {
        return $this->typeMatiere ?? self::TYPE_MATIERE_COURS;
    }

    public function setTypeMatiere(?string $typeMatiere): self
    {
        $this->typeMatiere = $typeMatiere;

        return $this;
    }

    /**
     * @return Collection<int, HistoriqueFicheMatiere>
     */
    public function getHistoriqueFicheMatieres(): Collection
    {
        return $this->historiqueFicheMatieres;
    }

    public function addHistoriqueFicheMatiere(HistoriqueFicheMatiere $historiqueFicheMatiere): static
    {
        if (!$this->historiqueFicheMatieres->contains($historiqueFicheMatiere)) {
            $this->historiqueFicheMatieres->add($historiqueFicheMatiere);
            $historiqueFicheMatiere->setFicheMatiere($this);
        }

        return $this;
    }

    public function removeHistoriqueFicheMatiere(HistoriqueFicheMatiere $historiqueFicheMatiere): static
    {
        if ($this->historiqueFicheMatieres->removeElement($historiqueFicheMatiere)) {
            // set the owning side to null (unless already changed)
            if ($historiqueFicheMatiere->getFicheMatiere() === $this) {
                $historiqueFicheMatiere->setFicheMatiere(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getVolumeCmPresentiel(): ?float
    {
        return $this->volumeCmPresentiel ?? 0;
    }

    public function setVolumeCmPresentiel(?float $volumeCmPresentiel = 0.0): self
    {
        $this->volumeCmPresentiel = $volumeCmPresentiel;

        return $this;
    }

    public function getVolumeTdPresentiel(): ?float
    {
        return $this->volumeTdPresentiel ?? 0;
    }

    public function setVolumeTdPresentiel(?float $volumeTdPresentiel = 0.0): self
    {
        $this->volumeTdPresentiel = $volumeTdPresentiel;

        return $this;
    }

    public function getVolumeTpPresentiel(): ?float
    {
        return $this->volumeTpPresentiel ?? 0;
    }

    public function setVolumeTpPresentiel(?float $volumeTpPresentiel = 0.0): self
    {
        $this->volumeTpPresentiel = $volumeTpPresentiel;

        return $this;
    }

    public function getVolumeTe(): ?float
    {
        return $this->volumeTe ?? 0;
    }

    public function setVolumeTe(?float $volumeTe = 0.0): self
    {
        $this->volumeTe = $volumeTe;

        return $this;
    }

    public function getEtatMccc(): ?string
    {
        return $this->etatMccc;
    }

    public function setEtatMccc(?string $etatMccc): static
    {
        $this->etatMccc = $etatMccc;

        return $this;
    }

    /**
     * @return Collection<int, Mccc>
     */
    public function getMcccs(): Collection
    {
        return $this->mcccs;
    }

    public function addMccc(Mccc $mccc): static
    {
        if (!$this->mcccs->contains($mccc)) {
            $this->mcccs->add($mccc);
            $mccc->setFicheMatiere($this);
        }

        return $this;
    }

    public function removeMccc(Mccc $mccc): static
    {
        if ($this->mcccs->removeElement($mccc)) {
            // set the owning side to null (unless already changed)
            if ($mccc->getFicheMatiere() === $this) {
                $mccc->setFicheMatiere(null);
            }
        }

        return $this;
    }

    public function etatStructure(): string
    {
        $cmPresentiel = $this->volumeCmPresentiel ?? 0.0;
        $tdPresentiel = $this->volumeTdPresentiel ?? 0.0;
        $tpPresentiel = $this->volumeTpPresentiel ?? 0.0;
        $cmDistanciel = $this->volumeCmDistanciel ?? 0.0;
        $tdDistanciel = $this->volumeTdDistanciel ?? 0.0;
        $tpDistanciel = $this->volumeTpDistanciel ?? 0.0;
        $volumeTe = $this->volumeTe ?? 0.0;

        $nbHeures = $cmPresentiel + $tdPresentiel + $tpPresentiel + $volumeTe + $cmDistanciel + $tdDistanciel + $tpDistanciel;

        if ($nbHeures === 0.0 && $this->isSansHeures() === false) {
            return 'À compléter';
        }

        return 'Complet';
    }

    /**
     * @return Collection<int, ButApprentissageCritique>
     */
    public function getApprentissagesCritiques(): Collection
    {
        return $this->apprentissagesCritiques;
    }

    public function addApprentissagesCritique(ButApprentissageCritique $apprentissagesCritique): static
    {
        if (!$this->apprentissagesCritiques->contains($apprentissagesCritique)) {
            $this->apprentissagesCritiques->add($apprentissagesCritique);
        }

        return $this;
    }

    public function removeApprentissagesCritique(ButApprentissageCritique $apprentissagesCritique): static
    {
        $this->apprentissagesCritiques->removeElement($apprentissagesCritique);

        return $this;
    }

    public function getSemestre(): int
    {
        if ($this->getElementConstitutifs()->count() > 0) {
            return $this->getElementConstitutifs()->first()->getUe()?->getSemestre()?->getOrdre();
        }
        return 0;
    }

    public function getVolumeEtudiant(): float
    {
        return $this->getVolumeCmPresentiel() + $this->getVolumeTdPresentiel() + $this->getVolumeTpPresentiel() + $this->getVolumeTe();
    }

    public function isSansNote(): ?bool
    {
        return $this->sansNote;
    }

    public function setSansNote(?bool $sansNote): static
    {
        $this->sansNote = $sansNote;

        return $this;
    }

    public function isSansHeures(): ?bool
    {
        return $this->sansHeures ?? false;
    }

    public function setSansHeures(?bool $sansHeures): static
    {
        $this->sansHeures = $sansHeures;

        return $this;
    }

    public function getVolumeCmDistanciel(): ?float
    {
        return $this->volumeCmDistanciel;
    }

    public function setVolumeCmDistanciel(?float $volumeCmDistanciel): static
    {
        $this->volumeCmDistanciel = $volumeCmDistanciel;

        return $this;
    }

    public function getVolumeTdDistanciel(): ?float
    {
        return $this->volumeTdDistanciel;
    }

    public function setVolumeTdDistanciel(?float $volumeTdDistanciel): static
    {
        $this->volumeTdDistanciel = $volumeTdDistanciel;

        return $this;
    }

    public function getVolumeTpDistanciel(): ?float
    {
        return $this->volumeTpDistanciel;
    }

    public function setVolumeTpDistanciel(?float $volumeTpDistanciel): static
    {
        $this->volumeTpDistanciel = $volumeTpDistanciel;

        return $this;
    }

    public function getTypeMccc(): ?string
    {
        return $this->typeMccc;
    }

    public function setTypeMccc(?string $typeMccc): static
    {
        $this->typeMccc = $typeMccc;

        return $this;
    }

    public function isHorsDiplome(): ?bool
    {
        return $this->horsDiplome ?? false;
    }

    public function setHorsDiplome(?bool $horsDiplome): static
    {
        $this->horsDiplome = $horsDiplome;

        return $this;
    }

    public function getTypeDiplome(): ?TypeDiplome
    {
        return $this->typeDiplome;
    }

    public function setTypeDiplome(?TypeDiplome $typeDiplome): static
    {
        $this->typeDiplome = $typeDiplome;

        return $this;
    }

    public function isVolumesHorairesImpose(): ?bool
    {
        return $this->volumesHorairesImpose ?? false;
    }

    public function setVolumesHorairesImpose(?bool $volumesHorairesImpose): static
    {
        $this->volumesHorairesImpose = $volumesHorairesImpose;

        return $this;
    }

    public function isEctsImpose(): ?bool
    {
        return $this->ectsImpose ?? false;
    }

    public function setEctsImpose(?bool $ectsImpose): static
    {
        $this->ectsImpose = $ectsImpose;

        return $this;
    }

    public function isMcccImpose(): ?bool
    {
        return $this->mcccImpose ?? false;
    }

    public function setMcccImpose(?bool $mcccImpose): static
    {
        $this->mcccImpose = $mcccImpose;

        return $this;
    }

    public function getEcts(): ?float
    {
        return $this->ects;
    }

    public function setEcts(?float $ects): static
    {
        $this->ects = $ects;

        return $this;
    }

    /**
     * @return Collection<int, Composante>
     */
    public function getComposante(): Collection
    {
        return $this->composante;
    }

    public function addComposante(Composante $composante): static
    {
        if (!$this->composante->contains($composante)) {
            $this->composante->add($composante);
        }

        return $this;
    }

    public function removeComposante(Composante $composante): static
    {
        $this->composante->removeElement($composante);

        return $this;
    }

    /**
     * @return Collection<int, CommentaireFicheMatiere>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(CommentaireFicheMatiere $commentaireFicheMatiere): static
    {
        if (!$this->commentaires->contains($commentaireFicheMatiere)) {
            $this->commentaires->add($commentaireFicheMatiere);
            $commentaireFicheMatiere->setFicheMatiere($this);
        }

        return $this;
    }

    public function removeCommentaire(CommentaireFicheMatiere $commentaireFicheMatiere): static
    {
        if ($this->commentaires->removeElement($commentaireFicheMatiere)) {
            // set the owning side to null (unless already changed)
            if ($commentaireFicheMatiere->getFicheMatiere() === $this) {
                $commentaireFicheMatiere->setFicheMatiere(null);
            }
        }

        return $this;
    }

    public function getEtatFiche(): ?array
    {
        return $this->etatFiche ?? [];
    }

    public function setEtatFiche(?array $etatFiche): static
    {
        $this->etatFiche = $etatFiche;

        return $this;
    }

    public function getLanguesSupportsArray(): array
    {
        $langues = [];
        foreach ($this->getLangueSupport() as $langue) {
            $langues[] = $langue->getLibelle();
        }

        return $langues;
    }

    public function getLanguesDispenseArray(): array
    {
        $langues = [];
        foreach ($this->getLangueDispense() as $langue) {
            $langues[] = $langue->getLibelle();
        }

        return $langues;
    }
}
