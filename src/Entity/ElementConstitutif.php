<?php

namespace App\Entity;

use App\Entity\Traits\LifeCycleTrait;
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

    #[ORM\Column(length: 30, nullable: true, enumType: ModaliteEnseignementEnum::class )]
    private ?ModaliteEnseignementEnum $modaliteEnseignement = null;

    #[ORM\Column]
    private ?float $ects = 0;

    #[ORM\Column]
    private ?float $volumeCmPresentiel = 0;

    #[ORM\Column]
    private ?float $volumeTdPresentiel = 0;

    #[ORM\Column]
    private ?float $volumeTpPresentiel = 0;

    #[ORM\Column]
    private ?float $volumeCmDistanciel = 0;

    #[ORM\Column]
    private ?float $volumeTdDistanciel = 0;

    #[ORM\Column]
    private ?float $volumeTpDistanciel = 0;

    #[ORM\Column]
    private ?bool $isCmPresentielMutualise = true;

    #[ORM\Column]
    private ?bool $isTdPresentielMutualise = true;

    #[ORM\Column]
    private ?bool $isTpPresentielMutualise = true;

    #[ORM\Column]
    private ?bool $isCmDistancielMutualise = true;

    #[ORM\Column]
    private ?bool $isTdDistancielMutualise = true;

    #[ORM\Column]
    private ?bool $isTpDistancielMutualise = true;

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

    #[ORM\Column(length: 50)]
    private ?string $etat_ec = 'initialise';

    public function __construct()
    {
        $this->competences = new ArrayCollection();
        $this->langueDispense = new ArrayCollection();
        $this->langueSupport = new ArrayCollection();
        $this->ecUes = new ArrayCollection();
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

        if ($this->ects === 0.0){
            return 'Pas d\'ECTS';
        }

        if ($this->modaliteEnseignement === null ) {
            return 'Modalité d\'enseignement non renseignée';
        }

        return 'Complet';
    }

    public function etatMcc(): string
    {
        if ($this->competences->isEmpty()) {
            return 'Non complété';
        }

        return 'Complet';
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
        if ($this->ecUes->removeElement($ecUe)) {
            // set the owning side to null (unless already changed)
            if ($ecUe->getEc() === $this) {
                $ecUe->setEc(null);
            }
        }

        return $this;
    }

    public function getParcours(): Parcours
    {
        //todo: à revoir, pourquoi first et pas autre ?
        return $this->getEcUes()->first()?->getUe()?->getSemestre()?->getSemestreParcours()->first()->getParcours();

    }

    public function getAllParcours(): Collection
    {
        //todo: à revoir, pourquoi first et pas autre ?
        return $this->getEcUes()->first()?->getUe()?->getSemestre()?->getSemestreParcours();

    }

    public function getEtatEc(): ?string
    {
        return $this->etat_ec;
    }

    public function setEtatEc(string $etat_ec): self
    {
        $this->etat_ec = $etat_ec;

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
}
