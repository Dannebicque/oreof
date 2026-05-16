<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/HeuresEctsAnnee.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/07/2023 17:34
 */

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\Groups;

class HeuresEctsAnnee
{
    #[Groups(['DTO_json_versioning'])]
    public float $sommeAnneeEcts = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeAnneeCmPres = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeAnneeTdPres = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeAnneeTpPres = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeAnneeTePres = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeAnneeCmDist = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeAnneeTdDist = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $sommeAnneeTpDist = 0;


    public function sommeAnneeTotalDist(): float
    {
        return $this->sommeAnneeCmDist + $this->sommeAnneeTdDist + $this->sommeAnneeTpDist;
    }

    public function sommeAnneeTotalPres(): float
    {
        return $this->sommeAnneeCmPres + $this->sommeAnneeTdPres + $this->sommeAnneeTpPres;
    }

    public function sommeAnneeTotalPresDist(): float
    {
        return $this->sommeAnneeTotalPres() + $this->sommeAnneeTotalDist();
    }

    public function addSemestre(HeuresEctsSemestre $dtoSemestre): void
    {
        $this->sommeAnneeCmPres += $dtoSemestre->sommeSemestreCmPres;
        $this->sommeAnneeTdPres += $dtoSemestre->sommeSemestreTdPres;
        $this->sommeAnneeTpPres += $dtoSemestre->sommeSemestreTpPres;
        $this->sommeAnneeTePres += $dtoSemestre->sommeSemestreTePres;
        $this->sommeAnneeCmDist += $dtoSemestre->sommeSemestreCmDist;
        $this->sommeAnneeTdDist += $dtoSemestre->sommeSemestreTdDist;
        $this->sommeAnneeTpDist += $dtoSemestre->sommeSemestreTpDist;
        $this->sommeAnneeEcts += $dtoSemestre->sommeSemestreEcts;
    }
}
