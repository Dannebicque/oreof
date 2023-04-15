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
use App\Repository\FicheMatiereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FicheMatiereRepository::class)]
#[ORM\HasLifecycleCallbacks]
class FicheMatiere
{
    use LifeCycleTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['fiche_matiere:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 250)]
    #[Groups(['fiche_matiere:read'])]
    private ?string $libelle = null;

    #[ORM\Column(length: 250, nullable: true)]
    #[Groups(['fiche_matiere:read'])]
    private ?string $libelleAnglais = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['fiche_matiere:read'])]
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

    #[ORM\Column(nullable: true)]
    private ?bool $isCmPresentielMutualise = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isTdPresentielMutualise = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isTpPresentielMutualise = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isCmDistancielMutualise = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isTdDistancielMutualise = null;

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

    #[ORM\OneToMany(mappedBy: 'ficheMatiere', targetEntity: FicheMatiereMutualisable::class)]
    private Collection $ficheMatiereParcours;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['fiche_matiere:read'])]
    private ?string $sigle = null;

    public function __construct()
    {
        $this->competences = new ArrayCollection();
        $this->langueDispense = new ArrayCollection();
        $this->langueSupport = new ArrayCollection();

        for ($i = 1; $i <= 5; $i++) {
            $this->etatSteps[$i] = false;
        }
        $this->elementConstitutifs = new ArrayCollection();
        $this->ficheMatiereParcours = new ArrayCollection();
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

        if ($this->competences->isEmpty() === false) {
            $nbChampsRemplis++;
        }

        $nbChampsObligatoires = 6;

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
            $texte .= ' (' . $this->sigle . ')';
        }

        return $texte;
    }
}
