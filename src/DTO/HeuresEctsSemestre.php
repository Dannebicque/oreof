<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/HeuresEctsSemestre.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/07/2023 17:34
 */

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;

class HeuresEctsSemestre
{
    #[Groups(['DTO_json_versioning'])]
    public float $sommeSemestreEcts = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeSemestreCmPres = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeSemestreTdPres = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeSemestreTpPres = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeSemestreTePres = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeSemestreCmDist = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeSemestreTdDist = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeSemestreTpDist = 0;


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

    public function addUe(HeuresEctsUe $dtoUe): void
    {
        $this->sommeSemestreCmPres += $dtoUe->sommeUeCmPres;
        $this->sommeSemestreTdPres += $dtoUe->sommeUeTdPres;
        $this->sommeSemestreTpPres += $dtoUe->sommeUeTpPres;
        $this->sommeSemestreTePres += $dtoUe->sommeUeTePres;
        $this->sommeSemestreCmDist += $dtoUe->sommeUeCmDist;
        $this->sommeSemestreTdDist += $dtoUe->sommeUeTdDist;
        $this->sommeSemestreTpDist += $dtoUe->sommeUeTpDist;
        $this->sommeSemestreEcts += $dtoUe->sommeUeEcts;
    }
}
