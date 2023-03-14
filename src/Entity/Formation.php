<?php

namespace App\Entity;

use App\Entity\Traits\LifeCycleTrait;
use App\Enums\EtatRemplissageEnum;
use App\Enums\NiveauFormationEnum;
use App\Enums\RegimeInscriptionEnum;
use App\Repository\FormationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Formation
{
    use LifeCycleTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $typeDiplome = null;

    #[ORM\ManyToOne]
    private ?Domaine $domaine = null;

    #[ORM\ManyToOne]
    private ?Composante $composantePorteuse = null;

    #[ORM\ManyToOne]
    private ?AnneeUniversitaire $anneeUniversitaire;

    #[ORM\ManyToOne]
    private ?Mention $mention = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mentionTexte = null;

    #[ORM\Column(type: Types::INTEGER, enumType: NiveauFormationEnum::class)]
    private ?NiveauFormationEnum $niveauEntree = null;

    #[ORM\Column(type: Types::INTEGER, enumType: NiveauFormationEnum::class)]
    private ?NiveauFormationEnum $niveauSortie = null;

    #[ORM\Column]
    private ?bool $inRncp = true;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $codeRNCP = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'formationsResponsableMention')]
    private ?User $responsableMention = null;

    #[ORM\Column]
    private ?int $semestreDebut = 1;

    #[ORM\ManyToMany(targetEntity: Ville::class)]
    private Collection $localisationMention;

    #[ORM\ManyToMany(targetEntity: Composante::class, inversedBy: 'formations')]
    private Collection $composantesInscription;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $regimeInscriptionTexte = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $modalitesAlternance = null;

    #[ORM\Column(nullable: true)]
    private ?array $regimeInscription = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $objectifsFormation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contenuFormation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resultatsAttendus = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rythmeFormationTexte = null;

    #[ORM\Column]
    private ?bool $hasParcours = false;

    #[ORM\Column(nullable: true)]
    private ?array $structureSemestres = [];

    #[ORM\OneToMany(mappedBy: 'formation', targetEntity: Parcours::class)]
    private Collection $parcours;

    #[ORM\OneToMany(mappedBy: 'formation', targetEntity: BlocCompetence::class)]
    private Collection $blocCompetences;

    #[ORM\ManyToOne]
    private ?RythmeFormation $rythmeFormation = null;

    #[ORM\Column(nullable: true)]
    private ?array $etatDpe = [];

    #[ORM\OneToMany(mappedBy: 'formation', targetEntity: UserCentre::class, cascade: ['persist', 'remove'])]
    private Collection $userCentres;

    #[ORM\Column(length: 10)]
    private ?string $version = '0.1';

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'formationsAnterieures')]
    private ?self $versionParent = null;

    #[ORM\OneToMany(mappedBy: 'versionParent', targetEntity: self::class)]
    private Collection $formationsAnterieures;

    #[ORM\Column]
    private ?array $etatSteps = [];

    public function __construct(AnneeUniversitaire $anneeUniversitaire)
    {
        $this->anneeUniversitaire = $anneeUniversitaire;
        $this->localisationMention = new ArrayCollection();
        $this->parcours = new ArrayCollection();
        $this->composantesInscription = new ArrayCollection();
        $this->blocCompetences = new ArrayCollection();
        $this->userCentres = new ArrayCollection();
        $this->formationsAnterieures = new ArrayCollection();

        for ($i = 0; $i < 5; $i++) {
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

    public function getTypeDiplome(): ?string
    {
        return $this->typeDiplome;
    }

    public function setTypeDiplome(string $typeDiplome): self
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

    public function getComposantePorteuse(): ?Composante
    {
        return $this->composantePorteuse;
    }

    public function setComposantePorteuse(?Composante $composantePorteuse): self
    {
        $this->composantePorteuse = $composantePorteuse;

        return $this;
    }

    public function getAnneeUniversitaire(): ?AnneeUniversitaire
    {
        return $this->anneeUniversitaire;
    }

    public function setAnneeUniversitaire(?AnneeUniversitaire $anneeUniversitaire): self
    {
        $this->anneeUniversitaire = $anneeUniversitaire;

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

    public function getNiveauEntree(): ?NiveauFormationEnum
    {
        return $this->niveauEntree;
    }

    public function setNiveauEntree(NiveauFormationEnum $niveauEntree): self
    {
        $this->niveauEntree = $niveauEntree;

        return $this;
    }

    public function getNiveauSortie(): ?NiveauFormationEnum
    {
        return $this->niveauSortie;
    }

    public function setNiveauSortie(NiveauFormationEnum $niveauSortie): self
    {
        $this->niveauSortie = $niveauSortie;

        return $this;
    }

    public function isInRncp(): ?bool
    {
        return $this->inRncp;
    }

    public function setInRncp(bool $inRncp): self
    {
        $this->inRncp = $inRncp;

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

    public function getSemestreDebut(): ?int
    {
        return $this->semestreDebut;
    }

    public function setSemestreDebut(int $semestreDebut): self
    {
        $this->semestreDebut = $semestreDebut;

        return $this;
    }

    /**
     * @return Collection<int, Ville>
     */
    public function getLocalisationMention(): Collection
    {
        return $this->localisationMention;
    }

    public function addLocalisationMention(Ville $localisationMention): self
    {
        if (!$this->localisationMention->contains($localisationMention)) {
            $this->localisationMention->add($localisationMention);
        }

        return $this;
    }

    public function removeLocalisationMention(Ville $localisationMention): self
    {
        $this->localisationMention->removeElement($localisationMention);

        return $this;
    }

    /**
     * @return Collection<int, Composante>
     */
    public function getComposantesInscription(): Collection
    {
        return $this->composantesInscription;
    }

    public function addComposantesInscription(Composante $composantesInscription): self
    {
        if (!$this->composantesInscription->contains($composantesInscription)) {
            $this->composantesInscription->add($composantesInscription);
        }

        return $this;
    }

    public function removeComposantesInscription(Composante $composantesInscription): self
    {
        $this->composantesInscription->removeElement($composantesInscription);

        return $this;
    }


    public function getRegimeInscriptionTexte(): ?string
    {
        return $this->regimeInscriptionTexte;
    }

    public function setRegimeInscriptionTexte(?string $regimeInscriptionTexte): self
    {
        $this->regimeInscriptionTexte = $regimeInscriptionTexte;

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

    public function display(): ?string
    {
        return $this->getMention() === null ? $this->getMentionTexte() : $this->getMention()->getLibelle();
    }

    public function getRegimeInscription(): array
    {
        $t = [];
        foreach ($this->regimeInscription as $value) {
            $t[] = RegimeInscriptionEnum::from($value);
        }


        return $t;
    }

    public function setRegimeInscription(?array $regimeInscription): self
    {
        $this->regimeInscription = $regimeInscription;

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

    public function isHasParcours(): ?bool
    {
        return $this->hasParcours;
    }

    public function setHasParcours(bool $hasParcours): self
    {
        $this->hasParcours = $hasParcours;

        return $this;
    }

    public function getStructureSemestres(): array
    {
        return $this->structureSemestres ?? [];
    }

    public function setStructureSemestres(?array $structureSemestres): self
    {
        $this->structureSemestres = $structureSemestres;

        return $this;
    }

    /**
     * @return Collection<int, Parcours>
     */
    public function getParcours(): Collection
    {
        return $this->parcours;
    }

    public function addParcour(Parcours $parcour): self
    {
        if (!$this->parcours->contains($parcour)) {
            $this->parcours->add($parcour);
            $parcour->setFormation($this);
        }

        return $this;
    }

    public function removeParcour(Parcours $parcour): self
    {
        // set the owning side to null (unless already changed)
        if ($this->parcours->removeElement($parcour) && $parcour->getFormation() === $this) {
            $parcour->setFormation(null);
        }

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
        // set the owning side to null (unless already changed)
        if ($this->blocCompetences->removeElement($blocCompetence) && $blocCompetence->getFormation() === $this) {
            $blocCompetence->setFormation(null);
        }

        return $this;
    }

    public function remplissage(): float
    {
        $total = 0;
        $total += $this->getMention() === null ? 0 : 1;
        $total += $this->getMentionTexte() === null ? 0 : 1;
        $total += count($this->getRegimeInscription()) === 0 ? 0 : 1;
        $total += $this->getContenuFormation() === null ? 0 : 1;
        $total += $this->getResultatsAttendus() === null ? 0 : 1;
        $total += $this->getRythmeFormation() === null ? 0 : 1;
        $total += $this->getRythmeFormationTexte() === null ? 0 : 1;
        $total += $this->isHasParcours() === null ? 0 : 1;
        $total += count($this->getStructureSemestres()) === 0 ? 0 : 1;

        return $total / 9 * 100;
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

    public function getEtatDpe(): array
    {
        return $this->etatDpe ?? [];
    }

    public function setEtatDpe(?array $etatDpe): self
    {
        $this->etatDpe = $etatDpe;

        return $this;
    }

    /**
     * @return Collection<int, UserCentre>
     */
    public function getUserCentres(): Collection
    {
        return $this->userCentres;
    }

    public function addUserCentre(UserCentre $userCentre): self
    {
        if (!$this->userCentres->contains($userCentre)) {
            $this->userCentres->add($userCentre);
            $userCentre->setFormation($this);
        }

        return $this;
    }

    public function removeUserCentre(UserCentre $userCentre): self
    {
        // set the owning side to null (unless already changed)
        if ($this->userCentres->removeElement($userCentre) && $userCentre->getFormation() === $this) {
            $userCentre->setFormation(null);
        }

        return $this;
    }

    public function onglets(): array
    {
        $onglets[0] = $this->getEtatOnglet0();
        $onglets[1] = $this->getEtatOnglet1();
        $onglets[2] = $this->getEtatOnglet2();
        $onglets[3] = $this->getEtatOnglet3();
        $onglets[4] = $this->getEtatOnglet4();

        return $onglets;
    }

    public function getEtatOnglet0(): EtatRemplissageEnum
    {
        return ($this->getEtatStep(0) && $this->semestreDebut !== 0) ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS;
    }

    public function getEtatOnglet1(): EtatRemplissageEnum
    {
        return $this->getLocalisationMention()->count() === 0 && $this->getComposantesInscription()->count() === 0 && count($this->getRegimeInscription()) === 0 ? EtatRemplissageEnum::VIDE : ($this->getEtatStep(1) && $this->getLocalisationMention()->count() > 0 && $this->getComposantesInscription()->count() > 0 && count($this->getRegimeInscription()) > 0 ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS); //todo: gérer la zone de saisie selon le type de rythme choisit
    }

    public function getEtatOnglet2(): EtatRemplissageEnum
    {
        return $this->getObjectifsFormation() === null && $this->getContenuFormation() === null && $this->getResultatsAttendus() === null && $this->getRythmeFormation() === null ? EtatRemplissageEnum::VIDE : ($this->getEtatStep(2) && $this->getObjectifsFormation() !== null && $this->getContenuFormation() !== null && $this->getResultatsAttendus() !== null && $this->getRythmeFormation() !== null ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS);
    }

    public function getEtatOnglet3(): EtatRemplissageEnum
    {
        //todo: ajouter les vérifs?
        return $this->getEtatStep(3) ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS;
    }

    public function getEtatOnglet4(): EtatRemplissageEnum
    {
        //todo: ajouter les vérifs?
        return $this->getEtatStep(4) ? EtatRemplissageEnum::COMPLETE : EtatRemplissageEnum::EN_COURS;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getVersionParent(): ?self
    {
        return $this->versionParent;
    }

    public function setVersionParent(?self $versionParent): self
    {
        $this->versionParent = $versionParent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getFormationsAnterieures(): Collection
    {
        return $this->formationsAnterieures;
    }

    public function addFormationsAnterieure(self $formationsAnterieure): self
    {
        if (!$this->formationsAnterieures->contains($formationsAnterieure)) {
            $this->formationsAnterieures->add($formationsAnterieure);
            $formationsAnterieure->setVersionParent($this);
        }

        return $this;
    }

    public function removeFormationsAnterieure(self $formationsAnterieure): self
    {
        // set the owning side to null (unless already changed)
        if ($this->formationsAnterieures->removeElement($formationsAnterieure) && $formationsAnterieure->getVersionParent() === $this) {
            $formationsAnterieure->setVersionParent(null);
        }

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

    public function valideStep(mixed $value)
    {
        switch ((int) $value) {
            case 0:
                return true;
            case 1:
                return false;
            case 2:
                return false;
        }
    }

    public function getObjectifsFormation(): ?string
    {
        return $this->objectifsFormation;
    }

    public function setObjectifsFormation(?string $objectifsFormation): self
    {
        $this->objectifsFormation = $objectifsFormation;

        return $this;
    }
}
