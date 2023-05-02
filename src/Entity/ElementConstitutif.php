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
use App\Repository\ElementConstitutifRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\OneToMany(mappedBy: 'ec', targetEntity: Mccc::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $mcccs;

    #[ORM\Column(length: 15)]
    private ?string $code = null;

    #[ORM\Column]
    private ?int $ordre = null;

    #[ORM\ManyToOne(inversedBy: 'elementConstitutifs')]
    private ?FicheMatiere $ficheMatiere = null;

    #[ORM\ManyToOne()]
    private ?Parcours $parcours = null;

    #[ORM\ManyToOne(inversedBy: 'elementConstitutifs')]
    private ?Ue $ue = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $texteEcLibre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(inversedBy: 'elementConstitutifs')]
    private ?TypeEc $typeEc = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'ecEnfants')]
    private ?self $ecParent = null;

    #[ORM\OneToMany(mappedBy: 'ecParent', targetEntity: self::class)]
    #[ORM\OrderBy(['ordre' => 'ASC'])]
    private Collection $ecEnfants;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $typeMccc = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $etatMccc = null;

    public function __construct()
    {
        $this->mcccs = new ArrayCollection();
        $this->ecEnfants = new ArrayCollection();
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

    public function setEcts(?float $ects): self
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
        $nbHeures = $this->volumeCmPresentiel ?? 0.0 + $this->volumeTdPresentiel ?? 0.0 + $this->volumeTpPresentiel ?? 0.0 + $this->volumeCmDistanciel ?? 0.0 + $this->volumeTdDistanciel ?? 0.0 + $this->volumeTpDistanciel ?? 0.0;

        if ($nbHeures === 0.0 && $this->modaliteEnseignement === null) {
            return 'À compléter';
        }

        if ($nbHeures === 0.0) {
            return 'Pas d\'heures';
        }

        if ($this->modaliteEnseignement === null) {
            return 'Volumes horaires non renseignés';
        }

        return 'Complet';
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
        if ($this->ecParent === null) {
            $this->setCode('EC ' . $this->ordre);
        } else {
            $this->setCode('EC ' . $this->ecParent->getOrdre() . '.' . chr($this->ordre + 64));
        }
    }

    public function display(): string
    {
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

    public function getTexteEcLibre(): ?string
    {
        return $this->texteEcLibre;
    }

    public function setTexteEcLibre(?string $texteEcLibre): self
    {
        $this->texteEcLibre = $texteEcLibre;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getTypeEc(): ?TypeEc
    {
        return $this->typeEc;
    }

    public function setTypeEc(?TypeEc $typeEc): self
    {
        $this->typeEc = $typeEc;

        return $this;
    }

    public function getEcParent(): ?self
    {
        return $this->ecParent;
    }

    public function setEcParent(?self $ecParent): self
    {
        $this->ecParent = $ecParent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getEcEnfants(): Collection
    {
        return $this->ecEnfants;
    }

    public function addEcEnfant(self $ecEnfant): self
    {
        if (!$this->ecEnfants->contains($ecEnfant)) {
            $this->ecEnfants->add($ecEnfant);
            $ecEnfant->setEcParent($this);
        }

        return $this;
    }

    public function removeEcEnfant(self $ecEnfant): self
    {
        if ($this->ecEnfants->removeElement($ecEnfant)) {
            // set the owning side to null (unless already changed)
            if ($ecEnfant->getEcParent() === $this) {
                $ecEnfant->setEcParent(null);
            }
        }

        return $this;
    }

    public function getTypeMccc(): ?string
    {
        return $this->typeMccc;
    }

    public function setTypeMccc(?string $typeMccc): self
    {
        $this->typeMccc = $typeMccc;

        return $this;
    }

    public function getEtatMccc(): ?string
    {
        return $this->etatMccc === null ? 'A Compléter' : $this->etatMccc;
    }

    public function setEtatMccc(?string $etatMccc): self
    {
        $this->etatMccc = $etatMccc;

        return $this;
    }
}
