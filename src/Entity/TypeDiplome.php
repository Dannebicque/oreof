<?php

namespace App\Entity;

use App\Repository\TypeDiplomeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeDiplomeRepository::class)]
class TypeDiplome
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $libelle_court = null;

    #[ORM\Column]
    private ?int $semestreDebut = null;

    #[ORM\Column]
    private ?int $semestreFin = null;

    #[ORM\Column]
    private ?int $nbUeMin = null;

    #[ORM\Column]
    private ?int $nbUeMax = null;

    #[ORM\Column]
    private ?int $nbEctsMaxUe = null;

    #[ORM\Column(length: 255)]
    private ?string $ModeleMcc = null;

    #[ORM\OneToMany(mappedBy: 'typeDiplome', targetEntity: Formation::class)]
    private Collection $formations;

    #[ORM\OneToMany(mappedBy: 'typeDiplome', targetEntity: Mention::class)]
    private Collection $mentions;

    #[ORM\ManyToMany(targetEntity: TypeEc::class, mappedBy: 'typeDiplomes')]
    private Collection $typeEcs;

    #[ORM\ManyToMany(targetEntity: TypeUe::class, mappedBy: 'typeDiplomes')]
    private Collection $typeUes;

    #[ORM\ManyToMany(targetEntity: TypeEpreuve::class, mappedBy: 'typeDiplomes')]
    private Collection $typeEpreuves;

    #[ORM\Column]
    private ?bool $debutSemestreFlexible = null;

    #[ORM\Column]
    private ?bool $hasStage = true;

    #[ORM\Column]
    private ?bool $hasProjet = true;

    #[ORM\Column]
    private ?bool $hasSituationPro = false;

    #[ORM\Column]
    private ?bool $hasMemoire = true;

    #[ORM\Column]
    private ?int $nbEcParUe = null;

    #[ORM\OneToMany(mappedBy: 'typeDiplome', targetEntity: FormationDemande::class)]
    private Collection $formationDemandes;

    #[ORM\OneToMany(mappedBy: 'typeDiplome', targetEntity: FicheMatiere::class)]
    private Collection $ficheMatieres;

    public function __construct()
    {
        $this->formations = new ArrayCollection();
        $this->mentions = new ArrayCollection();
        $this->typeEcs = new ArrayCollection();
        $this->typeUes = new ArrayCollection();
        $this->typeEpreuves = new ArrayCollection();
        $this->formationDemandes = new ArrayCollection();
        $this->ficheMatieres = new ArrayCollection();
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

    public function getLibelleCourt(): ?string
    {
        return $this->libelle_court;
    }

    public function setLibelleCourt(?string $libelle_court): self
    {
        $this->libelle_court = $libelle_court;

        return $this;
    }

    public function getSemestreDebut(): ?int
    {
        return $this->semestreDebut;
    }

    public function setSemestreDebut(int $semestreDebut): self
    {
        $this->semestreDebut = $semestreDebut;

        return $this;
    }

    public function getSemestreFin(): ?int
    {
        return $this->semestreFin;
    }

    public function setSemestreFin(int $semestreFin): self
    {
        $this->semestreFin = $semestreFin;

        return $this;
    }

    public function getNbUeMin(): ?int
    {
        return $this->nbUeMin;
    }

    public function setNbUeMin(int $nbUeMin): self
    {
        $this->nbUeMin = $nbUeMin;

        return $this;
    }

    public function getNbUeMax(): ?int
    {
        return $this->nbUeMax;
    }

    public function setNbUeMax(int $nbUeMax): self
    {
        $this->nbUeMax = $nbUeMax;

        return $this;
    }

    public function getNbEctsMaxUe(): ?int
    {
        return $this->nbEctsMaxUe;
    }

    public function setNbEctsMaxUe(int $nbEctsMaxUe): self
    {
        $this->nbEctsMaxUe = $nbEctsMaxUe;

        return $this;
    }

    public function getModeleMcc(): ?string
    {
        return $this->ModeleMcc;
    }

    public function setModeleMcc(string $ModeleMcc): self
    {
        $this->ModeleMcc = $ModeleMcc;

        return $this;
    }

    /**
     * @return Collection<int, Formation>
     */
    public function getFormations(): Collection
    {
        return $this->formations;
    }

    public function addFormation(Formation $formation): self
    {
        if (!$this->formations->contains($formation)) {
            $this->formations->add($formation);
            $formation->setTypeDiplome($this);
        }

        return $this;
    }

    public function removeFormation(Formation $formation): self
    {
        if ($this->formations->removeElement($formation)) {
            // set the owning side to null (unless already changed)
            if ($formation->getTypeDiplome() === $this) {
                $formation->setTypeDiplome(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Mention>
     */
    public function getMentions(): Collection
    {
        return $this->mentions;
    }

    public function addMention(Mention $mention): self
    {
        if (!$this->mentions->contains($mention)) {
            $this->mentions->add($mention);
            $mention->setTypeDiplome($this);
        }

        return $this;
    }

    public function removeMention(Mention $mention): self
    {
        if ($this->mentions->removeElement($mention)) {
            // set the owning side to null (unless already changed)
            if ($mention->getTypeDiplome() === $this) {
                $mention->setTypeDiplome(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TypeEc>
     */
    public function getTypeEcs(): Collection
    {
        return $this->typeEcs;
    }

    public function addTypeEc(TypeEc $typeEc): self
    {
        if (!$this->typeEcs->contains($typeEc)) {
            $this->typeEcs->add($typeEc);
            $typeEc->addTypeDiplome($this);
        }

        return $this;
    }

    public function removeTypeEc(TypeEc $typeEc): self
    {
        if ($this->typeEcs->removeElement($typeEc)) {
            $typeEc->removeTypeDiplome($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, TypeUe>
     */
    public function getTypeUes(): Collection
    {
        return $this->typeUes;
    }

    public function addTypeUe(TypeUe $typeUe): self
    {
        if (!$this->typeUes->contains($typeUe)) {
            $this->typeUes->add($typeUe);
            $typeUe->addTypeDiplome($this);
        }

        return $this;
    }

    public function removeTypeUe(TypeUe $typeUe): self
    {
        if ($this->typeUes->removeElement($typeUe)) {
            $typeUe->removeTypeDiplome($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, TypeEpreuve>
     */
    public function getTypeEpreuves(): Collection
    {
        return $this->typeEpreuves;
    }

    public function addTypeEpreufe(TypeEpreuve $typeEpreufe): self
    {
        if (!$this->typeEpreuves->contains($typeEpreufe)) {
            $this->typeEpreuves->add($typeEpreufe);
            $typeEpreufe->addTypeDiplome($this);
        }

        return $this;
    }

    public function removeTypeEpreufe(TypeEpreuve $typeEpreufe): self
    {
        if ($this->typeEpreuves->removeElement($typeEpreufe)) {
            $typeEpreufe->removeTypeDiplome($this);
        }

        return $this;
    }

    public function isDebutSemestreFlexible(): ?bool
    {
        return $this->debutSemestreFlexible;
    }

    public function setDebutSemestreFlexible(bool $debutSemestreFlexible): self
    {
        $this->debutSemestreFlexible = $debutSemestreFlexible;

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

    public function isHasProjet(): ?bool
    {
        return $this->hasProjet;
    }

    public function setHasProjet(bool $hasProjet): self
    {
        $this->hasProjet = $hasProjet;

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

    public function isHasMemoire(): ?bool
    {
        return $this->hasMemoire;
    }

    public function setHasMemoire(bool $hasMemoire): self
    {
        $this->hasMemoire = $hasMemoire;

        return $this;
    }

    public function getNbEcParUe(): ?int
    {
        return $this->nbEcParUe;
    }

    public function setNbEcParUe(int $nbEcParUe): self
    {
        $this->nbEcParUe = $nbEcParUe;

        return $this;
    }

    /**
     * @return Collection<int, FormationDemande>
     */
    public function getFormationDemandes(): Collection
    {
        return $this->formationDemandes;
    }

    public function addFormationDemande(FormationDemande $formationDemande): self
    {
        if (!$this->formationDemandes->contains($formationDemande)) {
            $this->formationDemandes->add($formationDemande);
            $formationDemande->setTypeDiplome($this);
        }

        return $this;
    }

    public function removeFormationDemande(FormationDemande $formationDemande): self
    {
        if ($this->formationDemandes->removeElement($formationDemande)) {
            // set the owning side to null (unless already changed)
            if ($formationDemande->getTypeDiplome() === $this) {
                $formationDemande->setTypeDiplome(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FicheMatiere>
     */
    public function getFicheMatieres(): Collection
    {
        return $this->ficheMatieres;
    }

    public function addFicheMatiere(FicheMatiere $ficheMatiere): static
    {
        if (!$this->ficheMatieres->contains($ficheMatiere)) {
            $this->ficheMatieres->add($ficheMatiere);
            $ficheMatiere->setTypeDiplome($this);
        }

        return $this;
    }

    public function removeFicheMatiere(FicheMatiere $ficheMatiere): static
    {
        if ($this->ficheMatieres->removeElement($ficheMatiere)) {
            // set the owning side to null (unless already changed)
            if ($ficheMatiere->getTypeDiplome() === $this) {
                $ficheMatiere->setTypeDiplome(null);
            }
        }

        return $this;
    }
}
