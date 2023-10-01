<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/StructureSemestre.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 01/10/2023 08:48
 */

namespace App\DTO;

use App\Entity\Semestre;
use App\Entity\Ue;

class StructureSemestre
{
    public Semestre $semestre;
    public bool $raccroche = false;
    public array $ues = [];
    public HeuresEctsSemestre $heuresEctsSemestre;

    public function __construct(Semestre $semestre, bool $raccroche = false)
    {
        $this->semestre = $semestre;
        $this->raccroche = $raccroche;
        $this->heuresEctsSemestre = new HeuresEctsSemestre();
    }

    public function addUe(?int $idUe, StructureUe $structureUe): void
    {
        $this->ues[$idUe] = $structureUe;
        $this->heuresEctsSemestre->addUe($structureUe->getHeuresEctsUe());
    }
}
