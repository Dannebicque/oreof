<?php

namespace App\Entity;

use App\Repository\HelpRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HelpRepository::class)]
class Help
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    private ?string $routeSlug = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    /**
     * @var Collection<int, Profil>
     */
    #[ORM\ManyToMany(targetEntity: Profil::class)]
    #[ORM\JoinTable(name: 'help_profil_access')]
    private Collection $profilsAutorises;

    public function __construct()
    {
        $this->profilsAutorises = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getRouteSlug(): ?string
    {
        return $this->routeSlug;
    }

    public function setRouteSlug(string $routeSlug): static
    {
        $this->routeSlug = $routeSlug;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection<int, Profil>
     */
    public function getProfilsAutorises(): Collection
    {
        return $this->profilsAutorises;
    }

    public function setProfilsAutorises(Collection $profilsAutorises): static
    {
        $this->profilsAutorises = $profilsAutorises;

        return $this;
    }

    public function addProfilAutorise(Profil $profil): static
    {
        if (!$this->profilsAutorises->contains($profil)) {
            $this->profilsAutorises->add($profil);
        }

        return $this;
    }

    public function removeProfilAutorise(Profil $profil): static
    {
        $this->profilsAutorises->removeElement($profil);

        return $this;
    }
}
