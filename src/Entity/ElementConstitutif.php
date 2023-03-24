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
    private ?NatureUeEc $natureUeEc = null;

    #[ORM\OneToMany(mappedBy: 'ec', targetEntity: Mccc::class, cascade: ['persist', 'remove'], orphanRemoval: true  )]
    private Collection $mcccs;

    #[ORM\Column(length: 5)]
    private ?string $code = null;

    #[ORM\Column]
    private ?int $ordre = null;

    #[ORM\ManyToOne(inversedBy: 'elementConstitutifs')]
    private ?FicheMatiere $ficheMatiere = null;

    #[ORM\ManyToOne()]
    private ?Parcours $parcours = null;

    #[ORM\ManyToOne(inversedBy: 'elementConstitutifs')]
    private ?Ue $ue = null;

    #[ORM\Column(nullable: true)]
    private ?int $subOrdre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $texteEcLibre = null;

    public function __construct()
    {
        $this->mcccs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getParcours(): ?Parcours
    {
        return $this->parcours;
    }

    public function setParcours(?Parcours $parcours): self
    {
        $this->parcours = $parcours;

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

    public function getNatureUeEc(): ?NatureUeEc
    {
        return $this->natureUeEc;
    }

    public function setNatureUeEc(?NatureUeEc $natureUeEc): self
    {
        $this->natureUeEc = $natureUeEc;

        return $this;
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
        if ($this->subOrdre === null || $this->subOrdre === 0) {
            $this->setCode('EC' . $this->ordre);
        } else {
            $this->setCode('EC' . $this->ordre . '.' . chr($this->subOrdre + 64));
        }
    }

    public function display() {
        if ($this->ficheMatiere !== null) {
            return $this->ficheMatiere->getLibelle();
        }

        return $this->texteEcLibre;
    }

    public function getFicheMatiere(): ?FicheMatiere
    {
        return $this->ficheMatiere;
    }

    public function setFicheMatiere(?FicheMatiere $ficheMatiere): self
    {
        $this->ficheMatiere = $ficheMatiere;

        return $this;
    }

    public function getUe(): ?Ue
    {
        return $this->ue;
    }

    public function setUe(?Ue $ue): self
    {
        $this->ue = $ue;

        return $this;
    }

    public function getSubOrdre(): ?int
    {
        return $this->subOrdre;
    }

    public function setSubOrdre(?int $subOrdre): self
    {
        $this->subOrdre = $subOrdre;

        return $this;
    }

    public function getTexteEcLibre(): ?string
    {
        return $this->texteEcLibre;
    }

    public function setTexteEcLibre(?string $texteEcLibre): self
    {
        $this->texteEcLibre = $texteEcLibre;

        return $this;
    }
}
