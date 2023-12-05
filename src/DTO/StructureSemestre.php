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
    private array $ues = [];
    public int $ordre;
    public HeuresEctsSemestre $heuresEctsSemestre;

    public function __construct(Semestre $semestre, int $ordre, bool $raccroche = false)
    {
        $this->ordre = $ordre;
        $this->semestre = $semestre;
        $this->raccroche = $raccroche;
        $this->heuresEctsSemestre = new HeuresEctsSemestre();
    }

    public function addUe(?int $idUe, StructureUe $structureUe): void
    {
        $this->ues[$idUe] = $structureUe;
        $this->heuresEctsSemestre->addUe($structureUe->getHeuresEctsUe());
    }

    public function getAnnee(): int
    {
        //si ordre = 1 ou 2 alors année = 1
        //si ordre = 3 ou 4 alors année = 2
        //si ordre = 5 ou 6 alors année = 3

        switch ($this->ordre) {
            case 1:
            case 2:
                return 1;
            case 3:
            case 4:
                return 2;
            case 5:
            case 6:
                return 3;
            default:
                return 0;
        }
    }

    public function ues(): array
    {
        sort($this->ues);
        return $this->ues;
    }
}
