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

    public function addEc(ElementConstitutif $ec): void
    {
        $this->totalCmPresentiel += $ec->getVolumeCmPresentiel();
        $this->totalTdPresentiel += $ec->getVolumeTdPresentiel();
        $this->totalTpPresentiel += $ec->getVolumeTpPresentiel();

        $this->totalCmDistanciel += $ec->getVolumeCmDistanciel();
        $this->totalTdDistanciel += $ec->getVolumeTdDistanciel();
        $this->totalTpDistanciel += $ec->getVolumeTpDistanciel();

        $this->totalVolumeTe += $ec->getVolumeTe();
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
        return $this->getTotalPresentiel() + $this->getTotalDistanciel();
    }
}
