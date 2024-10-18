<?php

namespace App\Entity;

use App\Repository\HistoriqueFormationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoriqueFormationRepository::class)]
class HistoriqueFormation extends Historique
{

    #[ORM\ManyToOne(inversedBy: 'historiqueFormations')]
    private ?Formation $formation = null;

    #[ORM\ManyToOne(inversedBy: 'historiqueFormations')]
    private ?ChangeRf $changeRf = null;


    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    public function setFormation(?Formation $formation): static
    {
        $this->formation = $formation;

        return $this;
    }

    public function getChangeRf(): ?ChangeRf
    {
        return $this->changeRf;
    }

    public function setChangeRf(?ChangeRf $changeRf): static
    {
        $this->changeRf = $changeRf;

        return $this;
    }
}
