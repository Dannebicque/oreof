<?php

namespace App\Entity;

use App\Enums\CentreGestionEnum;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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

    /**
     * @var string The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Etablissement $etablissement = null;

    #[ORM\OneToMany(mappedBy: 'directeur', targetEntity: Composante::class)]
    private Collection $composantes;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column]
    private ?bool $isEnable = false;

    #[ORM\Column]
    private ?bool $isValidDpe = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateValideDpe = null;

    #[ORM\Column]
    private ?bool $isValideAdministration = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateValideAdministration = null;

    #[ORM\Column(length: 30, nullable: true, enumType: CentreGestionEnum::class)]
    private ?CentreGestionEnum $centreDemande = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateDemande = null;

    #[ORM\Column(nullable: true)]
    private ?int $centreId = null;

    public function __construct()
    {
        $this->composantes = new ArrayCollection();
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
    public function getPassword(): string
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
    public function eraseCredentials()
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
        if ($this->composantes->removeElement($composante)) {
            // set the owning side to null (unless already changed)
            if ($composante->getDirecteur() === $this) {
                $composante->setDirecteur(null);
            }
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

    public function getDisplay(): string
    {
        return $this->getPrenom().' '.$this->getNom();
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

    public function getDateValideDpe(): ?\DateTimeInterface
    {
        return $this->dateValideDpe;
    }

    public function setDateValideDpe(\DateTimeInterface $dateValideDpe): self
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

    public function getDateValideAdministration(): ?\DateTimeInterface
    {
        return $this->dateValideAdministration;
    }

    public function setDateValideAdministration(?\DateTimeInterface $dateValideAdministration): self
    {
        $this->dateValideAdministration = $dateValideAdministration;

        return $this;
    }

    public function getCentreDemande(): ?CentreGestionEnum
    {
        return $this->centreDemande;
    }

    public function setCentreDemande(?CentreGestionEnum $centreDemande): self
    {
        $this->centreDemande = $centreDemande;

        return $this;
    }

    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->dateDemande;
    }

    public function setDateDemande(?\DateTimeInterface $dateDemande): self
    {
        $this->dateDemande = $dateDemande;

        return $this;
    }

    public function getCentreId(): ?int
    {
        return $this->centreId;
    }

    public function setCentreId(?int $centreId): self
    {
        $this->centreId = $centreId;

        return $this;
    }
}
