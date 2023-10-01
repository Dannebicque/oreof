<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/TotalVolumeHeure.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 26/05/2023 14:48
 */

namespace App\DTO;

use App\Entity\ElementConstitutif;

class TotalVolumeHeure
{
    public float $totalCmPresentiel = 0;
    public float $totalTdPresentiel = 0;
    public float $totalTpPresentiel = 0;

    public float $totalCmDistanciel = 0;
    public float $totalTdDistanciel = 0;
    public float $totalTpDistanciel = 0;
    public float $totalVolumeTe = 0;

    public function addSemestre(HeuresEctsSemestre $ec): void
    {
        $this->totalCmPresentiel += $ec->sommeSemestreCmPres;
        $this->totalTdPresentiel += $ec->sommeSemestreTdPres;
        $this->totalTpPresentiel += $ec->sommeSemestreTpPres;

        $this->totalCmDistanciel += $ec->sommeSemestreCmDist;
        $this->totalTdDistanciel += $ec->sommeSemestreTdDist;
        $this->totalTpDistanciel += $ec->sommeSemestreTpDist;

        $this->totalVolumeTe += $ec->sommeSemestreTePres;
    }

    public function getTotalPresentiel(): float
    {
        return $this->totalCmPresentiel + $this->totalTdPresentiel + $this->totalTpPresentiel;
    }

    public function getTotalVolumeTe(): float
    {
        return $this->totalVolumeTe;
    }

    public function getTotalDistanciel(): float
    {
        return $this->totalCmDistanciel + $this->totalTdDistanciel + $this->totalTpDistanciel;
    }

    public function getTotalEtudiant(): float
    {
        return $this->getTotalPresentiel() + $this->getTotalDistanciel() + $this->totalVolumeTe;
    }

    public function getVolumeTotal(): float
    {
        return $this->getTotalPresentiel() + $this->getTotalDistanciel();
    }
}
