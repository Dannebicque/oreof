<?php

namespace App\Entity;

use App\Repository\HistoriqueFicheMatiereRepository;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: HistoriqueFicheMatiereRepository::class)]
class HistoriqueFicheMatiere extends Historique
{

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'historiqueFicheMatieres')]
    private ?FicheMatiere $ficheMatiere = null;


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
