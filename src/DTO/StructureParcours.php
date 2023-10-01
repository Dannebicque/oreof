<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/StructureParcours.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 01/10/2023 08:43
 */

namespace App\DTO;

use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\Ue;

class StructureParcours
{
    public Parcours $parcours;
    public array $semestres = [];
    public HeuresEctsFormation $heuresEctsFormation;

    public function setParcours(Parcours $parcours): void
    {
        $this->parcours = $parcours;
        $this->heuresEctsFormation = new HeuresEctsFormation();

    }

    public function addSemestre(int $ordre, StructureSemestre $structureSemestre): void
    {
        $this->semestres[$ordre] = $structureSemestre;
        $this->heuresEctsFormation->addSemestre($structureSemestre->heuresEctsSemestre);
    }
}
