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

class HeuresEctsUe
{
    public float $sommeUeEcts = 0;
    public float $sommeUeCmPres = 0;
    public float $sommeUeTdPres = 0;
    public float $sommeUeTpPres = 0;
    public float $sommeUeTePres = 0;
    public float $sommeUeCmDist = 0;
    public float $sommeUeTdDist = 0;
    public float $sommeUeTpDist = 0;

    public function sommeUeTotalDist(): float
    {
        return $this->sommeUeCmDist + $this->sommeUeTdDist + $this->sommeUeTpDist;
    }

    public function sommeUeTotalPres(): float
    {
        return $this->sommeUeCmPres + $this->sommeUeTdPres + $this->sommeUeTpPres;
    }

    public function sommeUeTotalPresDist(): float {
        return $this->sommeUeTotalPres() + $this->sommeUeTotalDist();
    }

    public function addEc(ElementConstitutif $elementConstitutif)
    {
        //todo: tester si heures reprises ou pas
        $this->sommeUeCmPres += $elementConstitutif->getVolumeCmPresentiel();
        $this->sommeUeTdPres += $elementConstitutif->getVolumeTdPresentiel();
        $this->sommeUeTpPres += $elementConstitutif->getVolumeTpPresentiel();
        $this->sommeUeTePres += $elementConstitutif->getVolumeTe();
        $this->sommeUeCmDist += $elementConstitutif->getVolumeCmDistanciel();
        $this->sommeUeTdDist += $elementConstitutif->getVolumeTdDistanciel();
        $this->sommeUeTpDist += $elementConstitutif->getVolumeTpDistanciel();
        $this->sommeUeEcts += $elementConstitutif->getEcts();
    }
}
