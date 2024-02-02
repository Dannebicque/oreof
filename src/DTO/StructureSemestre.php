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
use App\Entity\SemestreParcours;

use Symfony\Component\Serializer\Annotation\Groups;

class StructureSemestre
{
    #[Groups(['DTO_json_versioning'])]
    public Semestre $semestre;

    public ?SemestreParcours $semestreParcours;

    #[Groups(['DTO_json_versioning'])]
    public bool $raccroche = false;

    #[Groups(['DTO_json_versioning'])]
    private array $ues = [];

    #[Groups(['DTO_json_versioning'])]
    public int $ordre;

    #[Groups(['DTO_json_versioning'])]
    public HeuresEctsSemestre $heuresEctsSemestre;
    private bool $withEcts = true;

    public function __construct(Semestre $semestre, int $ordre, bool $raccroche = false, SemestreParcours $semestreParcours = null, bool $withEcts = true)
    {
        $this->withEcts = $withEcts;
        $this->ordre = $ordre;
        $this->semestre = $semestre;
        $this->semestreParcours = $semestreParcours;
        $this->raccroche = $raccroche;
        if ($this->withEcts) {
            $this->heuresEctsSemestre = new HeuresEctsSemestre();
        }
    }

    public function addUe(?int $idUe, StructureUe $structureUe): void
    {
        if($idUe !== null){
            $this->ues[$idUe] = $structureUe;
        }
        else {
            $this->ues[] = $structureUe;
        }

        if ($this->withEcts) {
            $this->heuresEctsSemestre->addUe($structureUe->getHeuresEctsUe());
        }
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
