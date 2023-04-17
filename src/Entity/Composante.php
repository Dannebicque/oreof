<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/Composante.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 25/02/2023 23:23
 */

namespace App\Entity;

use App\Repository\ComposanteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ComposanteRepository::class)]
class Composante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(inversedBy: 'composantes')]
    private ?User $directeur = null;

    #[ORM\ManyToOne(inversedBy: 'composanteResponsableDpe')]
    private ?User $responsableDpe = null;

    #[ORM\OneToMany(targetEntity: Formation::class, mappedBy: 'composantePorteuse')]
    private Collection $formationsPortees;

    #[ORM\ManyToMany(targetEntity: Formation::class, mappedBy: 'composantesInscription')]
    private Collection $formations;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Adresse $adresse = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $telStandard = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $telComplementaire = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mailContact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $urlSite = null;

    #[ORM\OneToMany(mappedBy: 'composante', targetEntity: UserCentre::class)]
    private Collection $userCentres;

    #[ORM\Column(nullable: true)]
    private ?array $etatComposante = [];

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $sigle = null;

    public function __construct()
    {
        $this->formations = new ArrayCollection();
        $this->userCentres = new ArrayCollection();
        $this->formationsPortees = new ArrayCollection();
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

    public function remplissage(): float
    {
        $totalRemplissage = 0;
        $nbFormations = 0;
        foreach ($this->formations as $formation) {
            $totalRemplissage += $formation->remplissage();
            $nbFormations++;
        }

        return $nbFormations > 0 ? $totalRemplissage / $nbFormations : 0;
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
}
