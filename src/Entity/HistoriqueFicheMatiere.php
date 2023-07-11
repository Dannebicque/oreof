<?php

namespace App\Entity;

use App\Repository\HistoriqueFicheMatiereRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoriqueFicheMatiereRepository::class)]
class HistoriqueFicheMatiere extends Historique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'historiqueFicheMatieres')]
    private ?FicheMatiere $ficheMatiere = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFicheMatiere(): ?FicheMatiere
    {
        return $this->ficheMatiere;
    }

    public function setFicheMatiere(?FicheMatiere $ficheMatiere): static
    {
        $this->ficheMatiere = $ficheMatiere;

        return $this;
    }
}
