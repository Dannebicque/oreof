<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Entity/User.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 25/02/2023 23:23
 */

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    #[Ignore]
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Etablissement $etablissement = null;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'directeur', targetEntity: Composante::class)]
    private Collection $composantes;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'responsableDpe', targetEntity: Composante::class)]
    private Collection $composanteResponsableDpe;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'responsableMention', targetEntity: Formation::class)]
    private Collection $formationsResponsableMention;

    #[ORM\Column(length: 50)]
    #[Groups(['fiche_matiere:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    #[Groups(['fiche_matiere:read'])]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    #[Groups(['fiche_matiere:read'])]
    private ?string $email = null;

    #[ORM\Column]
    private ?bool $isEnable = false;

    #[ORM\Column]
    private ?bool $isValidDpe = false;

    #[Ignore]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateValideDpe = null;

    #[ORM\Column]
    private ?bool $isValideAdministration = false;

    #[Ignore]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateValideAdministration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateDemande = null;

    #[ORM\Column]
    private ?bool $isDeleted = false;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $civilite = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $telFixe = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $telPortable = null;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'destinataire', targetEntity: Notification::class)]
    private Collection $notifications;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserCentre::class)]
    private Collection $userCentres;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'coResponsable', targetEntity: Parcours::class)]
    private Collection $coParcours;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'coResponsable', targetEntity: Formation::class)]
    private Collection $coFormations;

    #[Ignore]
    #[ORM\ManyToOne]
    private ?Composante $composanteDemande = null;

    #[ORM\ManyToOne]
    private ?Etablissement $etablissementDemande = null;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Historique::class)]
    private Collection $historiques;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Commentaire::class)]
    private Collection $commentaires;

    public function __construct()
    {
        $this->composantes = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->userCentres = new ArrayCollection();
        $this->composanteResponsableDpe = new ArrayCollection();
        $this->formationsResponsableMention = new ArrayCollection();
        $this->coParcours = new ArrayCollection();
        $this->coFormations = new ArrayCollection();
        $this->historiques = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_LECTEUR';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    public function setEtablissement(?Etablissement $etablissement): self
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    /**
     * @return Collection<int, Composante>
     */
    public function getComposantes(): Collection
    {
        return $this->composantes;
    }

    public function addComposante(Composante $composante): self
    {
        if (!$this->composantes->contains($composante)) {
            $this->composantes->add($composante);
            $composante->setDirecteur($this);
        }

        return $this;
    }

    public function removeComposante(Composante $composante): self
    {
        // set the owning side to null (unless already changed)
        if ($this->composantes->removeElement($composante) && $composante->getDirecteur() === $this) {
            $composante->setDirecteur(null);
        }

        return $this;
    }

    public function getPrenom(): ?string
    {
        return ucwords(mb_strtolower($this->prenom));
    }

    public function setPrenom(?string $prenom): void
    {
        $this->prenom = $prenom;
    }

    public function getNom(): ?string
    {
        return mb_strtoupper($this->nom);
    }

    public function setNom(?string $nom): void
    {
        $this->nom = $nom;
    }

    #[Groups(['fiche_matiere:read'])]
    public function getDisplay(): string
    {
        return mb_strtoupper($this->getNom()).' '. ucwords(mb_strtolower($this->getPrenom()));
    }

    public function getAvatarInitiales(): ?string
    {
        return mb_strtoupper(mb_substr(trim($this->getPrenom()), 0, 1).mb_substr(trim($this->getNom()), 0, 1));
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function isIsEnable(): ?bool
    {
        return $this->isEnable;
    }

    public function setIsEnable(bool $isEnable): self
    {
        $this->isEnable = $isEnable;

        return $this;
    }

    public function isIsValidDpe(): ?bool
    {
        return $this->isValidDpe;
    }

    public function setIsValidDpe(bool $isValidDpe): self
    {
        $this->isValidDpe = $isValidDpe;

        return $this;
    }

    public function getDateValideDpe(): ?DateTimeInterface
    {
        return $this->dateValideDpe;
    }

    public function setDateValideDpe(DateTimeInterface $dateValideDpe): self
    {
        $this->dateValideDpe = $dateValideDpe;

        return $this;
    }

    public function isIsValideAdministration(): ?bool
    {
        return $this->isValideAdministration;
    }

    public function setIsValideAdministration(bool $isValideAdministration): self
    {
        $this->isValideAdministration = $isValideAdministration;

        return $this;
    }

    public function getDateValideAdministration(): ?DateTimeInterface
    {
        return $this->dateValideAdministration;
    }

    public function setDateValideAdministration(?DateTimeInterface $dateValideAdministration): self
    {
        $this->dateValideAdministration = $dateValideAdministration;

        return $this;
    }

    public function getDateDemande(): ?DateTimeInterface
    {
        return $this->dateDemande;
    }

    public function setDateDemande(?DateTimeInterface $dateDemande): self
    {
        $this->dateDemande = $dateDemande;

        return $this;
    }

    public function isIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getCivilite(): ?string
    {
        return $this->civilite;
    }

    public function setCivilite(?string $civilite): self
    {
        $this->civilite = $civilite;

        return $this;
    }

    public function getTelFixe(): ?string
    {
        return $this->telFixe;
    }

    public function setTelFixe(?string $telFixe): self
    {
        $this->telFixe = $telFixe;

        return $this;
    }

    public function getTelPortable(): ?string
    {
        return $this->telPortable;
    }

    public function setTelPortable(?string $telPortable): self
    {
        $this->telPortable = $telPortable;

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setDestinataire($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        // set the owning side to null (unless already changed)
        if ($this->notifications->removeElement($notification) && $notification->getDestinataire() === $this) {
            $notification->setDestinataire(null);
        }

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
            $userCentre->setUser($this);
        }

        return $this;
    }

    public function removeUserCentre(UserCentre $userCentre): self
    {
        // set the owning side to null (unless already changed)
        if ($this->userCentres->removeElement($userCentre) && $userCentre->getUser() === $this) {
            $userCentre->setUser(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, Composante>
     */
    public function getComposanteResponsableDpe(): Collection
    {
        return $this->composanteResponsableDpe;
    }

    public function addComposanteResponsableDpe(Composante $composanteResponsableDpe): self
    {
        if (!$this->composanteResponsableDpe->contains($composanteResponsableDpe)) {
            $this->composanteResponsableDpe->add($composanteResponsableDpe);
            $composanteResponsableDpe->setResponsableDpe($this);
        }

        return $this;
    }

    public function removeComposanteResponsableDpe(Composante $composanteResponsableDpe): self
    {
        // set the owning side to null (unless already changed)
        if ($this->composanteResponsableDpe->removeElement($composanteResponsableDpe) && $composanteResponsableDpe->getResponsableDpe() === $this) {
            $composanteResponsableDpe->setResponsableDpe(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, Formation>
     */
    public function getFormationsResponsableMention(): Collection
    {
        return $this->formationsResponsableMention;
    }

    public function addFormationsResponsableMention(Formation $formationsResponsableMention): self
    {
        if (!$this->formationsResponsableMention->contains($formationsResponsableMention)) {
            $this->formationsResponsableMention->add($formationsResponsableMention);
            $formationsResponsableMention->setResponsableMention($this);
        }

        return $this;
    }

    public function removeFormationsResponsableMention(Formation $formationsResponsableMention): self
    {
        // set the owning side to null (unless already changed)
        if ($this->formationsResponsableMention->removeElement($formationsResponsableMention) && $formationsResponsableMention->getResponsableMention() === $this) {
            $formationsResponsableMention->setResponsableMention(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, Parcours>
     */
    public function getCoParcours(): Collection
    {
        return $this->coParcours;
    }

    public function addCoParcour(Parcours $coParcour): self
    {
        if (!$this->coParcours->contains($coParcour)) {
            $this->coParcours->add($coParcour);
            $coParcour->setCoResponsable($this);
        }

        return $this;
    }

    public function removeCoParcour(Parcours $coParcour): self
    {
        if ($this->coParcours->removeElement($coParcour)) {
            // set the owning side to null (unless already changed)
            if ($coParcour->getCoResponsable() === $this) {
                $coParcour->setCoResponsable(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Formation>
     */
    public function getCoFormations(): Collection
    {
        return $this->coFormations;
    }

    public function addCoFormation(Formation $coFormation): self
    {
        if (!$this->coFormations->contains($coFormation)) {
            $this->coFormations->add($coFormation);
            $coFormation->setCoResponsable($this);
        }

        return $this;
    }

    public function removeCoFormation(Formation $coFormation): self
    {
        if ($this->coFormations->removeElement($coFormation)) {
            // set the owning side to null (unless already changed)
            if ($coFormation->getCoResponsable() === $this) {
                $coFormation->setCoResponsable(null);
            }
        }

        return $this;
    }

    public function getComposanteDemande(): ?Composante
    {
        return $this->composanteDemande;
    }

    public function setComposanteDemande(?Composante $composanteDemande): self
    {
        $this->composanteDemande = $composanteDemande;

        return $this;
    }

    public function getEtablissementDemande(): ?Etablissement
    {
        return $this->etablissementDemande;
    }

    public function setEtablissementDemande(?Etablissement $etablissementDemande): self
    {
        $this->etablissementDemande = $etablissementDemande;

        return $this;
    }

    /**
     * @return Collection<int, Historique>
     */
    public function getHistoriques(): Collection
    {
        return $this->historiques;
    }

    public function addHistorique(Historique $historique): static
    {
        if (!$this->historiques->contains($historique)) {
            $this->historiques->add($historique);
            $historique->setUser($this);
        }

        return $this;
    }

    public function removeHistorique(Historique $historique): static
    {
        if ($this->historiques->removeElement($historique)) {
            // set the owning side to null (unless already changed)
            if ($historique->getUser() === $this) {
                $historique->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setUser($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getUser() === $this) {
                $commentaire->setUser(null);
            }
        }

        return $this;
    }
}
