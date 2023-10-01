<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/HeuresEctsUe.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/07/2023 17:35
 */

namespace App\DTO;

use App\Entity\ElementConstitutif;

class HeuresEctsEc
{
    public float $ects = 0;
    public float $cmPres = 0;
    public float $tdPres = 0;
    public float $tpPres = 0;
    public float $tePres = 0;
    public float $cmDist = 0;
    public float $tdDist = 0;
    public float $tpDist = 0;

    public function sommeEcTotalDist(): float
    {
        return $this->cmDist + $this->tdDist + $this->tpDist;
    }

    public function sommeEcTotalPres(): float
    {
        return $this->cmPres + $this->tdPres + $this->tpPres;
    }

    public function sommeEcTotalPresDist(): float {
        return $this->sommeEcTotalPres() + $this->sommeEcTotalDist();
    }

    public function addEc(ElementConstitutif $elementConstitutif): void
    {
        $this->cmPres = $elementConstitutif->getVolumeCmPresentiel() ?? 0.0;
        $this->tdPres = $elementConstitutif->getVolumeTdPresentiel() ?? 0.0;
        $this->tpPres = $elementConstitutif->getVolumeTpPresentiel() ?? 0.0;
        $this->tePres = $elementConstitutif->getVolumeTe() ?? 0.0;
        $this->cmDist = $elementConstitutif->getVolumeCmDistanciel() ?? 0.0;
        $this->tdDist = $elementConstitutif->getVolumeTdDistanciel() ?? 0.0;
        $this->tpDist = $elementConstitutif->getVolumeTpDistanciel() ?? 0.0;
        $this->ects = $elementConstitutif->getEcts() ?? 0.0;
    }
}
