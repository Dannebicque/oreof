<?php

namespace App\Entity;

use App\Repository\HistoriqueParcoursRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoriqueParcoursRepository::class)]
class HistoriqueParcours extends Historique
{
    #[ORM\ManyToOne(inversedBy: 'historiqueParcours')]
    private ?Parcours $parcours = null;

    public function getParcours(): ?Parcours
    {
        return $this->parcours;
    }

    public function setParcours(?Parcours $parcours): static
    {
        $this->parcours = $parcours;

        return $this;
    }
}
