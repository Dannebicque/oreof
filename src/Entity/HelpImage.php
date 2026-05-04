<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file //wsl.localhost/Ubuntu/home/louca/oreof-stack/oreofv2/src/Entity/HelpImage.php
 * @author louca
 * @project oreofv2
 * @lastUpdate 04/05/2026 14:19
 */

namespace App\Entity;

use App\Repository\HelpImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: HelpImageRepository::class)]
class HelpImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['help_image:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['help_image:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups(['help_image:read'])]
    private ?string $fichier = null;

    #[ORM\Column]
    #[Groups(['help_image:read'])]
    private ?\DateTimeImmutable $dateCreation = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getFichier(): ?string
    {
        return $this->fichier;
    }

    public function setFichier(string $fichier): static
    {
        $this->fichier = $fichier;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }
}

