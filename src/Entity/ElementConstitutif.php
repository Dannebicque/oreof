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
use App\Enums\EtatRemplissageEnum;
use App\Enums\ModaliteEnseignementEnum;
use App\Repository\ElementConstitutifRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ElementConstitutifRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ElementConstitutif
{
    use LifeCycleTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 250)]
    private ?string $libelle = null;

    #[ORM\Column(length: 250, nullable: true)]
    private ?string $libelleAnglais = null;

    #[ORM\Column]
    private ?bool $enseignementMutualise = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $objectifs = null;

    #[ORM\ManyToMany(targetEntity: Competence::class, inversedBy: 'elementConstitutifs')]
    private Collection $competences;

    #[ORM\Column(length: 30, nullable: true, enumType: ModaliteEnseignementEnum::class)]
    private ?ModaliteEnseignementEnum $modaliteEnseignement = null;

    #[ORM\Column(nullable: true)]
    private ?float $ects;

    #[ORM\Column(nullable: true)]
    private ?float $volumeCmPresentiel;

    #[ORM\Column(nullable: true)]
    private ?float $volumeTdPresentiel;

    #[ORM\Column(nullable: true)]
    private ?float $volumeTpPresentiel;

    #[ORM\Column(nullable: true)]
    private ?float $volumeCmDistanciel;

    #[ORM\Column(nullable: true)]
    private ?float $volumeTdDistanciel;

    #[ORM\Column(nullable: true)]
    private ?float $volumeTpDistanciel;

    #[ORM\Column(nullable: true)]
    private ?bool $isCmPresentielMutualise;

    #[ORM\Column(nullable: true)]
    private ?bool $isTdPresentielMutualise;

    #[ORM\Column(nullable: true)]
    private ?bool $isTpPresentielMutualise;

    #[ORM\Column(nullable: true)]
    private ?bool $isCmDistancielMutualise;

    #[ORM\Column(nullable: true)]
    private ?bool $isTdDistancielMutualise;

    #[ORM\Column(nullable: true)]
    private ?bool $isTpDistancielMutualise;

    #[ORM\ManyToOne]
    private ?User $responsableEc = null;

    #[ORM\ManyToMany(targetEntity: Langue::class, inversedBy: 'elementConstitutifs')]
    #[ORM\JoinTable(name: 'element_constitutif_langue_dispense')]
    private Collection $langueDispense;

    #[ORM\ManyToMany(targetEntity: Langue::class, inversedBy: 'languesSupportsEcs')]
    #[ORM\JoinTable(name: 'element_constitutif_langue_support')]
    private Collection $langueSupport;

    #[ORM\ManyToOne]
    private ?TypeEnseignement $typeEnseignement = null;

    #[ORM\OneToMany(mappedBy: 'ec', targetEntity: EcUe::class)]
    private Collection $ecUes;

    #[ORM\Column(nullable: true)]
    private ?array $etatEc = [];

    #[ORM\OneToMany(mappedBy: 'ec', targetEntity: Mccc::class)]
    private Collection $mcccs;

    #[ORM\Column(length: 5)]
    private ?string $code = null;

    #[ORM\Column]
    private ?array $etatSteps = [];

    #[ORM\Column]
    private ?int $ordre = null;

    public function __construct()
    {
        $this->competences = new ArrayCollection();
        $this->langueDispense = new ArrayCollection();
        $this->langueSupport = new ArrayCollection();
        $this->ecUes = new ArrayCollection();
        $this->mcccs = new ArrayCollection();

        for ($i = 1; $i <= 5; $i++) {
            $this->etatSteps[$i] = false;
        }
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

    public function setEnseignementMutualise(bool $enseignementMutualise): self
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

    public function getModaliteEnseignement(): ?ModaliteEnseignementEnum
    {
        return $this->modaliteEnseignement;
    }

    public function setModaliteEnseignement(?ModaliteEnseignementEnum $modaliteEnseignement): self
    {
        $this->modaliteEnseignement = $modaliteEnseignement;

        return $this;
    }

    public function getEcts(): ?float
    {
        return $this->ects;
    }

    public function setEcts(float $ects): self
    {
        $this->ects = $ects;

        return $this;
    }

    public function getVolumeCmPresentiel(): ?float
    {
        return $this->volumeCmPresentiel;
    }

    public function setVolumeCmPresentiel(float $volumeCmPresentiel): self
    {
        $this->volumeCmPresentiel = $volumeCmPresentiel;

        return $this;
    }

    public function getVolumeTdPresentiel(): ?float
    {
        return $this->volumeTdPresentiel;
    }

    public function setVolumeTdPresentiel(float $volumeTdPresentiel): self
    {
        $this->volumeTdPresentiel = $volumeTdPresentiel;

        return $this;
    }

    public function getVolumeTpPresentiel(): ?float
    {
        return $this->volumeTpPresentiel;
    }

    public function setVolumeTpPresentiel(float $volumeTpPresentiel): self
    {
        $this->volumeTpPresentiel = $volumeTpPresentiel;

        return $this;
    }

    public function getVolumeCmDistanciel(): ?float
    {
        return $this->volumeCmDistanciel;
    }

    public function setVolumeCmDistanciel(float $volumeCmDistanciel): self
    {
        $this->volumeCmDistanciel = $volumeCmDistanciel;

        return $this;
    }

    public function getVolumeTdDistanciel(): ?float
    {
        return $this->volumeTdDistanciel;
    }

    public function setVolumeTdDistanciel(float $volumeTdDistanciel): self
    {
        $this->volumeTdDistanciel = $volumeTdDistanciel;

        return $this;
    }

    public function getVolumeTpDistanciel(): ?float
    {
        return $this->volumeTpDistanciel;
    }

    public function setVolumeTpDistanciel(float $volumeTpDistanciel): self
    {
        $this->volumeTpDistanciel = $volumeTpDistanciel;

        return $this;
    }

    public function getResponsableEc(): ?User
    {
        return $this->responsableEc;
    }

    public function setResponsableEc(?User $responsableEc): self
    {
        $this->responsableEc = $responsableEc;

        return $this;
    }

    public function remplissage(): float
    {
        //calcul un remplissage de l'entité en fonction des champs obligatoires
        $nbChampsRemplis = 0;

        if ($this->langueDispense->isEmpty() === false) {
            $nbChampsRemplis++;
        }

        if ($this->typeEnseignement !== null) {
            $nbChampsRemplis++;
        }

        if ($this->libelle !== null) {
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

        if ($this->competences->isEmpty() === false) {
            $nbChampsRemplis++;
        }


        $nbHeures = $this->volumeCmPresentiel + $this->volumeTdPresentiel + $this->volumeTpPresentiel + $this->volumeCmDistanciel + $this->volumeTdDistanciel + $this->volumeTpDistanciel;

        if ($nbHeures > 0.0) {
            $nbChampsRemplis++;
        }

        if ($this->modaliteEnseignement !== null) {
            $nbChampsRemplis++;
        }

        if ($this->ects > 0.0) {
            $nbChampsRemplis++;
        }

        $nbChampsObligatoires = 11;

        return round($nbChampsRemplis / $nbChampsObligatoires * 100, 2);
    }

    public function etatStructure(): string
    {
        $nbHeures = $this->volumeCmPresentiel + $this->volumeTdPresentiel + $this->volumeTpPresentiel + $this->volumeCmDistanciel + $this->volumeTdDistanciel + $this->volumeTpDistanciel;

        if ($nbHeures === 0.0 && $this->modaliteEnseignement === null && $this->ects === 0.0) {
            return 'Non complété';
        }

        if ($nbHeures === 0.0) {
            return 'Pas d\'heures';
        }

        if ($this->ects === 0.0) {
            return 'Pas d\'ECTS';
        }

        if ($this->modaliteEnseignement === null) {
            return 'Modalité d\'enseignement non renseignée';
        }

        return 'Complet';
    }

    public function etatMccc(): string
    {
        $totalPourcentage = [];
        $nbNotes = [];
        foreach ($this->getMcccs() as $mccc) {
            if (!isset($totalPourcentage[$mccc->getNumeroSession()])) {
                $totalPourcentage[$mccc->getNumeroSession()] = 0;
            }
            if (!isset($nbNotes[$mccc->getNumeroSession()])) {
                $nbNotes[$mccc->getNumeroSession()] = 0;
            }

            $totalPourcentage[$mccc->getNumeroSession()] += $mccc->getPourcentage();
            $nbNotes[$mccc->getNumeroSession()] += $mccc->getNbEpreuves();
        }

        $pourcentageOK = count($totalPourcentage) > 0;
        foreach ($totalPourcentage as $pourcentage) {
            if ($pourcentage !== 100.0) {
                $pourcentageOK = false;
            }
        }

        $nbNotesOK = count($nbNotes) > 0;
        foreach ($nbNotes as $nb) {
            if ($nb <= 0) {
                $nbNotesOK = false;
            }
        }

        return $pourcentageOK && $nbNotesOK ? 'Complet' : 'Non complet';
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

    public function isIsCmPresentielMutualise(): ?bool
    {
        return $this->isCmPresentielMutualise;
    }

    public function setIsCmPresentielMutualise(bool $isCmPresentielMutualise): self
    {
        $this->isCmPresentielMutualise = $isCmPresentielMutualise;

        return $this;
    }

    public function isIsTdPresentielMutualise(): ?bool
    {
        return $this->isTdPresentielMutualise;
    }

    public function setIsTdPresentielMutualise(bool $isTdPresentielMutualise): self
    {
        $this->isTdPresentielMutualise = $isTdPresentielMutualise;

        return $this;
    }

    public function isIsTpPresentielMutualise(): ?bool
    {
        return $this->isTpPresentielMutualise;
    }

    public function setIsTpPresentielMutualise(bool $isTpPresentielMutualise): self
    {
        $this->isTpPresentielMutualise = $isTpPresentielMutualise;

        return $this;
    }

    public function isIsCmDistancielMutualise(): ?bool
    {
        return $this->isCmDistancielMutualise;
    }

    public function setIsCmDistancielMutualise(bool $isCmDistancielMutualise): self
    {
        $this->isCmDistancielMutualise = $isCmDistancielMutualise;

        return $this;
    }

    public function isIsTdDistancielMutualise(): ?bool
    {
        return $this->isTdDistancielMutualise;
    }

    public function setIsTdDistancielMutualise(bool $isTdDistancielMutualise): self
    {
        $this->isTdDistancielMutualise = $isTdDistancielMutualise;

        return $this;
    }

    public function isIsTpDistancielMutualise(): ?bool
    {
        return $this->isTpDistancielMutualise;
    }

    public function setIsTpDistancielMutualise(bool $isTpDistancielMutualise): self
    {
        $this->isTpDistancielMutualise = $isTpDistancielMutualise;

        return $this;
    }

    public function getTypeEnseignement(): ?TypeEnseignement
    {
        return $this->typeEnseignement;
    }

    public function setTypeEnseignement(?TypeEnseignement $typeEnseignement): self
    {
        $this->typeEnseignement = $typeEnseignement;

        return $this;
    }

    /**
     * @return Collection<int, EcUe>
     */
    public function getEcUes(): Collection
    {
        return $this->ecUes;
    }

    public function addEcUe(EcUe $ecUe): self
    {
        if (!$this->ecUes->contains($ecUe)) {
            $this->ecUes->add($ecUe);
            $ecUe->setEc($this);
        }

        return $this;
    }

    public function removeEcUe(EcUe $ecUe): self
    {
        // set the owning side to null (unless already changed)
        if ($this->ecUes->removeElement($ecUe) && $ecUe->getEc() === $this) {
            $ecUe->setEc(null);
        }

        return $this;
    }

    public function getParcours(): Parcours
    {
        //todo: à revoir, pourquoi first et pas autre ?
        return $this->getEcUes()->first()->getUe()?->getSemestre()?->getSemestreParcours()->first()->getParcours();
    }

    public function getAllParcours(): Collection
    {
        //todo: à revoir, pourquoi first et pas autre ?
        return $this->getEcUes()->first()->getUe()?->getSemestre()?->getSemestreParcours();
    }

    public function getEtatEc(): array
    {
        return $this->etatEc ?? [];
    }

    public function setEtatEc(?array $etatEc): self
    {
        $this->etatEc = $etatEc;

        return $this;
    }

    public function domaineLibelle(): ?string
    {
        return $this->getParcours()->getFormation()?->getDomaine()?->getLibelle();
    }

    public function formationLibelle(): ?string
    {
        return $this->getParcours()->getFormation()?->display();
    }

    public function etatRemplissageOnglets(): array
    {
        // Onglet 1 : Identité de l'enseignement
        // Onglet 2 : Présentation
        // Onglet 3 : Objectifs et compétences
        // Onglet 4 : Structure et organisation pédagogiques
        // Onglet 5 : Modalités d'évaluation

        $onglets[1] = $this->getEtatOnglet1();
        $onglets[2] = $this->getEtatOnglet2();
        $onglets[3] = $this->getEtatOnglet3(); //todo: ajouter un flag pour savoir si les compétences sont complètes
        $onglets[4] = $this->getEtatOnglet4();
        $onglets[5] = $this->getEtatOnglet5();

        return $onglets;
    }

    public function getEtatOnglet1(): EtatRemplissageEnum
    {
        return $this->getLibelleAnglais() === null && $this->getLibelle() === null && $this->enseignementMutualise === null ? EtatRemplissageEnum::VIDE : (($this->getLibelleAnglais() !== null && $this->getLibelle() !== null && $this->enseignementMutualise !== null && $this->getEtatStep(1)) ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS);
    }

    public function getEtatOnglet2(): EtatRemplissageEnum
    {
        return $this->getDescription() === null && $this->getLangueDispense()->count() === 0 && $this->getLangueSupport()->count() === 0 && $this->getTypeEnseignement() === null ? EtatRemplissageEnum::VIDE : (($this->getDescription() !== null && $this->getLangueDispense()->count() > 0 && $this->getLangueSupport()->count() > 0 && $this->getTypeEnseignement() !== null && $this->getEtatStep(2)) ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS);
    }

    public function getEtatOnglet3(): EtatRemplissageEnum
    {
        return $this->getObjectifs() === null && $this->getCompetences() === null ? EtatRemplissageEnum::VIDE : (($this->getObjectifs() !== null && $this->getCompetences() !== null && $this->getEtatStep(3)) ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS);
    }

    public function getEtatOnglet4(): EtatRemplissageEnum
    {
        return $this->etatStructure() === 'Non complété' ? EtatRemplissageEnum::VIDE : (($this->etatStructure() === 'Complet' && $this->getEtatStep(4)) ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS);
    }

    public function getEtatOnglet5(): EtatRemplissageEnum
    {
        return $this->etatMccc() === 'Non complété' ? EtatRemplissageEnum::VIDE : (($this->etatMccc() === 'Complet' && $this->getEtatStep(5)) ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS);
    }

    /**
     * @return Collection<int, Mccc>
     */
    public function getMcccs(): Collection
    {
        return $this->mcccs;
    }

    public function addMccc(Mccc $mccc): self
    {
        if (!$this->mcccs->contains($mccc)) {
            $this->mcccs->add($mccc);
            $mccc->setEc($this);
        }

        return $this;
    }

    public function removeMccc(Mccc $mccc): self
    {
        // set the owning side to null (unless already changed)
        if ($this->mcccs->removeElement($mccc) && $mccc->getEc() === $this) {
            $mccc->setEc(null);
        }

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function genereCode(): void
    {
        $this->setCode('EC'.$this->ordre);
    }
}
