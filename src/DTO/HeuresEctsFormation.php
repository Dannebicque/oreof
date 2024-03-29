<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/HeuresFormation.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/07/2023 17:34
 */

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;

class HeuresEctsFormation
{
    #[Groups(['DTO_json_versioning'])]
    public float $sommeFormationEcts = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeFormationCmPres = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeFormationTdPres = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeFormationTpPres = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeFormationTePres = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeFormationCmDist = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeFormationTdDist = 0;
    
    #[Groups(['DTO_json_versioning'])]
    public float $sommeFormationTpDist = 0;

    public function sommeFormationTotalDist(): float
    {
        return $this->sommeFormationCmDist + $this->sommeFormationTdDist + $this->sommeFormationTpDist;
    }

    public function sommeFormationTotalPres(): float
    {
        return $this->sommeFormationCmPres + $this->sommeFormationTdPres + $this->sommeFormationTpPres;
    }

    public function sommeFormationTotalPresDist(): float
    {
        return $this->sommeFormationTotalPres() + $this->sommeFormationTotalDist();
    }

    public function addSemestre(HeuresEctsSemestre $dtoSemestre): void
    {
        $this->sommeFormationCmPres += $dtoSemestre->sommeSemestreCmPres;
        $this->sommeFormationTdPres += $dtoSemestre->sommeSemestreTdPres;
        $this->sommeFormationTpPres += $dtoSemestre->sommeSemestreTpPres;
        $this->sommeFormationTePres += $dtoSemestre->sommeSemestreTePres;
        $this->sommeFormationCmDist += $dtoSemestre->sommeSemestreCmDist;
        $this->sommeFormationTdDist += $dtoSemestre->sommeSemestreTdDist;
        $this->sommeFormationTpDist += $dtoSemestre->sommeSemestreTpDist;
        $this->sommeFormationEcts += $dtoSemestre->sommeSemestreEcts;
    }
}
