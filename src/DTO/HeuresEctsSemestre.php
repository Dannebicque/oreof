<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/HeuresEctsSemestre.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/07/2023 17:34
 */

namespace App\DTO;

class HeuresEctsSemestre
{
    public float $sommeSemestreEcts = 0;
    public float $sommeSemestreCmPres = 0;
    public float $sommeSemestreTdPres = 0;
    public float $sommeSemestreTpPres = 0;
    public float $sommeSemestreCmDist = 0;
    public float $sommeSemestreTdDist = 0;
    public float $sommeSemestreTpDist = 0;

    public array $ues = [];

    public function sommeSemestreTotalDist(): float
    {
        return $this->sommeSemestreCmDist + $this->sommeSemestreTdDist + $this->sommeSemestreTpDist;
    }

    public function sommeSemestreTotalPres(): float
    {
        return $this->sommeSemestreCmPres + $this->sommeSemestreTdPres + $this->sommeSemestreTpPres;
    }

    public function sommeSemestreTotalPresDist(): float
    {
        return $this->sommeSemestreTotalPres() + $this->sommeSemestreTotalDist();
    }

    public function addUe(?int $ordre, HeuresEctsUe $dtoUe): void
    {
        $this->ues[$ordre] = $dtoUe;
        $this->sommeSemestreCmPres += $dtoUe->sommeUeCmPres;
        $this->sommeSemestreTdPres += $dtoUe->sommeUeTdPres;
        $this->sommeSemestreTpPres += $dtoUe->sommeUeTpPres;
        $this->sommeSemestreCmDist += $dtoUe->sommeUeCmDist;
        $this->sommeSemestreTdDist += $dtoUe->sommeUeTdDist;
        $this->sommeSemestreTpDist += $dtoUe->sommeUeTpDist;
        $this->sommeSemestreEcts += $dtoUe->sommeUeEcts;
    }
}
