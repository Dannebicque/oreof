<?php

namespace App\Entity;

use App\Repository\CompetenceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetenceRepository::class)]
class Competence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'competences')]
    private ?BlocCompetence $blocCompetence = null;

    #[ORM\Column(length: 10)]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;


    public function __construct(BlocCompetence $blocCompetence)
    {
        $this->blocCompetence = $blocCompetence;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBlocCompetence(): ?BlocCompetence
    {
        return $this->blocCompetence;
    }

    public function setBlocCompetence(?BlocCompetence $blocCompetence): self
    {
        $this->blocCompetence = $blocCompetence;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
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
}
