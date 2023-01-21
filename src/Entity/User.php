<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    #[ORM\Column]
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

    public function setPassword(string $password): self
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
}
