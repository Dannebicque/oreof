<?php

namespace App\Entity;

use App\Enums\CentreGestionEnum;
use App\Enums\ModaliteEnseignementEnum;
use App\Enums\RythmeFormationEnum;
use App\Repository\FormationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormationRepository::class)]
class Formation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeDiplome = null;

    #[ORM\ManyToOne]
    private ?Domaine $domaine = null;

    #[ORM\ManyToOne]
    private ?Mention $mention = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mentionTexte = null;

    #[ORM\Column(length: 20)]
    private ?string $niveauEntree = null;

    #[ORM\Column(length: 20)]
    private ?string $niveauSortie = null;

    #[ORM\Column]
    private ?bool $inscriptionRNCP = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $codeRNCP = null;

    #[ORM\ManyToOne]
    private ?User $responsableMention = null;

    #[ORM\ManyToMany(targetEntity: Site::class, inversedBy: 'formations')]
    private Collection $sites;

    #[ORM\Column]
    private ?int $semestreDebut = 1;

    #[ORM\Column]
    private ?bool $hasParcours = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contenuFormation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resultatsAttendus = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rythmeFormationTexte = null;

    #[ORM\Column]
    private ?bool $hasStage = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $stageText = null;

    #[ORM\Column(nullable: true)]
    private ?float $nbHeuresStages = null;

    #[ORM\Column]
    private ?bool $hasProjet = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $projetText = null;

    #[ORM\Column]
    private ?float $nbHeuresProjet = null;

    #[ORM\Column]
    private ?bool $hasMemoire = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $memoireText = null;

    #[ORM\Column]
    private ?float $nbHeuresMemoire = null;

    #[ORM\Column(type: Types::INTEGER,  nullable: true, enumType: ModaliteEnseignementEnum::class)]
    private ?ModaliteEnseignementEnum $modalitesEnseignement = null;

    #[ORM\OneToMany(mappedBy: 'formation', targetEntity: BlocCompetence::class)]
    private Collection $blocCompetences;

    #[ORM\Column(type: Types::STRING, length: 30, nullable: true, enumType: RythmeFormationEnum::class)]
    private ?RythmeFormationEnum $rythmeFormation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $prerequis = null;

    public function __construct()
    {
        $this->sites = new ArrayCollection();
        $this->blocCompetences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeDiplome(): ?string
    {
        return $this->typeDiplome;
    }

    public function setTypeDiplome(?string $typeDiplome): self
    {
        $this->typeDiplome = $typeDiplome;

        return $this;
    }

    public function getDomaine(): ?Domaine
    {
        return $this->domaine;
    }

    public function setDomaine(?Domaine $domaine): self
    {
        $this->domaine = $domaine;

        return $this;
    }

    public function getMention(): ?Mention
    {
        return $this->mention;
    }

    public function setMention(?Mention $mention): self
    {
        $this->mention = $mention;

        return $this;
    }

    public function getMentionTexte(): ?string
    {
        return $this->mentionTexte;
    }

    public function setMentionTexte(?string $mentionTexte): self
    {
        $this->mentionTexte = $mentionTexte;

        return $this;
    }

    public function getNiveauEntree(): ?string
    {
        return $this->niveauEntree;
    }

    public function setNiveauEntree(string $niveauEntree): self
    {
        $this->niveauEntree = $niveauEntree;

        return $this;
    }

    public function getNiveauSortie(): ?string
    {
        return $this->niveauSortie;
    }

    public function setNiveauSortie(string $niveauSortie): self
    {
        $this->niveauSortie = $niveauSortie;

        return $this;
    }

    public function isInscriptionRNCP(): ?bool
    {
        return $this->inscriptionRNCP;
    }

    public function setInscriptionRNCP(bool $inscriptionRNCP): self
    {
        $this->inscriptionRNCP = $inscriptionRNCP;

        return $this;
    }

    public function getCodeRNCP(): ?string
    {
        return $this->codeRNCP;
    }

    public function setCodeRNCP(?string $codeRNCP): self
    {
        $this->codeRNCP = $codeRNCP;

        return $this;
    }

    public function getResponsableMention(): ?User
    {
        return $this->responsableMention;
    }

    public function setResponsableMention(?User $responsableMention): self
    {
        $this->responsableMention = $responsableMention;

        return $this;
    }

    /**
     * @return Collection<int, Site>
     */
    public function getSites(): Collection
    {
        return $this->sites;
    }

    public function addSite(Site $site): self
    {
        if (!$this->sites->contains($site)) {
            $this->sites->add($site);
        }

        return $this;
    }

    public function removeSite(Site $site): self
    {
        $this->sites->removeElement($site);

        return $this;
    }

    public function display()
    {
        //si la mention est renseignée, on affiche le nom de la mention
        if ($this->mention) {
            return $this->mention->getLibelle();
        }

        //sinon on affiche le texte de la mention
        return $this->typeDiplome . ' ' . $this->mentionTexte;
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

    public function isHasParcours(): ?bool
    {
        return $this->hasParcours;
    }

    public function setHasParcours(bool $hasParcours): self
    {
        $this->hasParcours = $hasParcours;

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

    public function typeDiplome(): string
    {
        if ($this->mention !== null) {
            return $this->mention->getTypeDiplome();
        }

        return $this->typeDiplome;
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

    public function getNbHeuresMemoire(): ?float
    {
        return $this->nbHeuresMemoire;
    }

    public function setNbHeuresMemoire(float $nbHeuresMemoire): self
    {
        $this->nbHeuresMemoire = $nbHeuresMemoire;

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
            $blocCompetence->setFormation($this);
        }

        return $this;
    }

    public function removeBlocCompetence(BlocCompetence $blocCompetence): self
    {
        if ($this->blocCompetences->removeElement($blocCompetence)) {
            // set the owning side to null (unless already changed)
            if ($blocCompetence->getFormation() === $this) {
                $blocCompetence->setFormation(null);
            }
        }

        return $this;
    }

    public function getRythmeFormation(): ?RythmeFormationEnum
    {
        return $this->rythmeFormation;
    }

    public function setRythmeFormation(?RythmeFormationEnum $rythmeFormation): self
    {
        $this->rythmeFormation = $rythmeFormation;

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
}
