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

use Symfony\Component\Serializer\Annotation\Groups;

class StructureParcours
{
    #[Groups(['DTO_json_versioning'])]
    public Parcours $parcours;

    #[Groups(['DTO_json_versioning'])]
    public array $semestres = [];

    #[Groups(['DTO_json_versioning'])]
    public HeuresEctsFormation $heuresEctsFormation;

    public StatsFichesMatieresParcours $statsFichesMatieresParcours;

    public function __construct(
        private $withEcts = true,
        private $withBcc = true,
    )
    {
    }
    public function setParcours(Parcours $parcours): void
    {
        $this->statsFichesMatieresParcours = new StatsFichesMatieresParcours($parcours);
        $this->parcours = $parcours;
        if ($this->withEcts) {
            $this->heuresEctsFormation = new HeuresEctsFormation();
        }

    }

    public function addSemestre(int $ordre, StructureSemestre $structureSemestre): void
    {
        $this->semestres[$ordre] = $structureSemestre;
        if ($this->withEcts) {
            $this->heuresEctsFormation->addSemestre($structureSemestre->heuresEctsSemestre);
        }
    }

    public function getTabAnnee(): array
    {
        $tab = [];
        foreach ($this->semestres as $semestre) {
            $tab[$semestre->getAnnee()][$semestre->ordre] = $semestre;
        }

        return $tab;
    }
}
