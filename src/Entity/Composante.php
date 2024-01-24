<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/Composante.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 25/02/2023 23:23
 */

namespace App\Entity;

use App\DTO\Remplissage;
use App\Repository\ComposanteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ComposanteRepository::class)]
class Composante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['parcours_json_versioning', 'fiche_matiere_versioning'])]
    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(inversedBy: 'composantes')]
    private ?User $directeur = null;

    #[ORM\ManyToOne(inversedBy: 'composanteResponsableDpe')]
    private ?User $responsableDpe = null;

    #[ORM\OneToMany(mappedBy: 'composantePorteuse', targetEntity: Formation::class)]
    private Collection $formationsPortees;

    #[ORM\ManyToMany(targetEntity: Formation::class, mappedBy: 'composantesInscription')]
    private Collection $formations;

    #[Groups('parcours_json_versioning')]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Adresse $adresse = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $telStandard = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $telComplementaire = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mailContact = null;

    #[Groups('parcours_json_versioning')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $urlSite = null;

    #[ORM\OneToMany(mappedBy: 'composante', targetEntity: UserCentre::class)]
    private Collection $userCentres;

    #[ORM\Column(nullable: true)]
    private ?array $etatComposante = [];

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $sigle = null;

    #[ORM\ManyToMany(targetEntity: FicheMatiere::class, mappedBy: 'composante')]
    private Collection $ficheMatieres;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $codeComposante = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $codeApogee = null;

    #[ORM\ManyToOne(inversedBy: 'composantes')]
    private ?Etablissement $etablissement = null;

    #[ORM\Column(nullable: true)]
    private ?bool $inscriptionUniquement = false;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'composantes')]
    private ?self $composanteParent = null;

    #[ORM\OneToMany(mappedBy: 'composanteParent', targetEntity: self::class)]
    private Collection $composantes;

    public function __construct()
    {
        $this->formations = new ArrayCollection();
        $this->userCentres = new ArrayCollection();
        $this->formationsPortees = new ArrayCollection();
        $this->ficheMatieres = new ArrayCollection();
        $this->composantes = new ArrayCollection();
    }

    public function getEtatComposante(): array
    {
        return $this->etatComposante ?? [];
    }

    public function setEtatComposante(?array $etatComposante): self
    {
        $this->etatComposante = $etatComposante;

        return $this;
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

    public function getDirecteur(): ?User
    {
        return $this->directeur;
    }

    public function setDirecteur(?User $directeur): self
    {
        $this->directeur = $directeur;

        return $this;
    }

    public function getResponsableDpe(): ?User
    {
        return $this->responsableDpe;
    }

    public function setResponsableDpe(?User $responsableDpe): self
    {
        $this->responsableDpe = $responsableDpe;

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
            $formation->addComposantesInscription($this);
        }

        return $this;
    }

    public function removeFormation(Formation $formation): self
    {
        if ($this->formations->removeElement($formation)) {
            $formation->removeComposantesInscription($this);
        }

        return $this;
    }

    public function getAdresse(): ?Adresse
    {
        return $this->adresse;
    }

    public function setAdresse(?Adresse $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function remplissage(): Remplissage
    {
        $remplissage = new Remplissage();
        foreach ($this->formations as $formation) {
            $remplissage->addRemplissage($formation->getRemplissage());
        }

        return $remplissage;
    }

    public function getTelStandard(): ?string
    {
        return $this->telStandard;
    }

    public function setTelStandard(?string $telStandard): self
    {
        $this->telStandard = $telStandard;

        return $this;
    }

    public function getTelComplementaire(): ?string
    {
        return $this->telComplementaire;
    }

    public function setTelComplementaire(?string $telComplementaire): self
    {
        $this->telComplementaire = $telComplementaire;

        return $this;
    }

    public function getMailContact(): ?string
    {
        return $this->mailContact;
    }

    public function setMailContact(?string $mailContact): self
    {
        $this->mailContact = $mailContact;

        return $this;
    }

    public function getUrlSite(): ?string
    {
        return $this->urlSite;
    }

    public function setUrlSite(?string $urlSite): self
    {
        $this->urlSite = $urlSite;

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
            $userCentre->setComposante($this);
        }

        return $this;
    }

    public function removeUserCentre(UserCentre $userCentre): self
    {
        // set the owning side to null (unless already changed)
        if ($this->userCentres->removeElement($userCentre) && $userCentre->getComposante() === $this) {
            $userCentre->setComposante(null);
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

    /**
     * @return Collection<int, Formation>
     */
    public function getFormationsPortees(): Collection
    {
        return $this->formationsPortees;
    }

    public function addFormationsPortee(Formation $formationsPortee): self
    {
        if (!$this->formationsPortees->contains($formationsPortee)) {
            $this->formationsPortees->add($formationsPortee);
            $formationsPortee->setComposantePorteuse($this);
        }

        return $this;
    }

    public function removeFormationsPortee(Formation $formationsPortee): self
    {
        if ($this->formationsPortees->removeElement($formationsPortee)) {
            // set the owning side to null (unless already changed)
            if ($formationsPortee->getComposantePorteuse() === $this) {
                $formationsPortee->setComposantePorteuse(null);
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
            $ficheMatiere->addComposante($this);
        }

        return $this;
    }

    public function removeFicheMatiere(FicheMatiere $ficheMatiere): static
    {
        if ($this->ficheMatieres->removeElement($ficheMatiere)) {
            $ficheMatiere->removeComposante($this);
        }

        return $this;
    }

    public function getCodeComposante(): ?string
    {
        return $this->codeComposante;
    }

    public function setCodeComposante(?string $codeComposante): static
    {
        $this->codeComposante = $codeComposante;

        return $this;
    }

    public function getCodeApogee(): ?string
    {
        return $this->codeApogee;
    }

    public function setCodeApogee(?string $codeApogee): static
    {
        $this->codeApogee = $codeApogee;

        return $this;
    }

    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    public function setEtablissement(?Etablissement $etablissement): static
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    public function isInscriptionUniquement(): ?bool
    {
        return $this->inscriptionUniquement ?? false;
    }

    public function setInscriptionUniquement(?bool $inscriptionUniquement): static
    {
        $this->inscriptionUniquement = $inscriptionUniquement;

        return $this;
    }

    public function getComposanteParent(): ?self
    {
        return $this->composanteParent;
    }

    public function setComposanteParent(?self $composanteParent): static
    {
        $this->composanteParent = $composanteParent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getComposantes(): Collection
    {
        return $this->composantes;
    }

    public function addComposante(self $composante): static
    {
        if (!$this->composantes->contains($composante)) {
            $this->composantes->add($composante);
            $composante->setComposanteParent($this);
        }

        return $this;
    }

    public function removeComposante(self $composante): static
    {
        if ($this->composantes->removeElement($composante)) {
            // set the owning side to null (unless already changed)
            if ($composante->getComposanteParent() === $this) {
                $composante->setComposanteParent(null);
            }
        }

        return $this;
    }
}
