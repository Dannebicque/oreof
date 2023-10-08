<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/StructureUe.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 01/10/2023 08:51
 */

namespace App\DTO;

use App\Entity\ElementConstitutif;
use App\Entity\Ue;

class StructureUe
{
    public string $display = '';
    public ?Ue $ueOrigine;
    public Ue $ue;
    public bool $raccroche = false;
    public array $elementConstitutifs = [];
    public array $uesEnfants = [];
    public array $heuresEctsUeEnfants = [];
    public HeuresEctsUe $heuresEctsUe;

    public function __construct(Ue $ue, bool $raccroche = false, ?string $display = null, ?Ue $ueOrigine = null)
    {
        $this->ue = $ue;
        $this->display = $display ?? '';
        $this->raccroche = $raccroche;
        $this->ueOrigine = $ueOrigine;
        $this->heuresEctsUe = new HeuresEctsUe();
    }

    public function addUeEnfant(?int $idUe, StructureUe $structureUe): void
    {
        $this->uesEnfants[$idUe] = $structureUe;
        $this->heuresEctsUeEnfants[$idUe] = $structureUe->heuresEctsUe;
        //gérer pour prendre le max des heures et ects sur tous les enfants de l'EC
    }

    public function addEc(StructureEc $structureEc): void
    {
        $this->elementConstitutifs[] = $structureEc;
        $this->heuresEctsUe->addEc($structureEc->getHeuresEctsEc());
    }

    public function ordre(): int
    {
        return $this->ueOrigine !== null ? $this->ueOrigine->getOrdre() : $this->ue->getOrdre();
    }

    public function getHeuresEctsUe(): HeuresEctsUe
    {
        if (count($this->heuresEctsUeEnfants) > 0) {
            //parcourir le tableau, comparer les objets et retourner celui dont la somme des heures est la plus grande
            foreach ($this->heuresEctsUeEnfants as $heuresEctsUeEnfant) {
                $this->heuresEctsUe->sommeUeEcts = max($this->heuresEctsUe->sommeUeEcts, $heuresEctsUeEnfant->sommeUeEcts);
                if ($heuresEctsUeEnfant->sommeUeTotalPres() > $this->heuresEctsUe->sommeUeTotalPres()) {
                    $this->heuresEctsUe = $heuresEctsUeEnfant;
                }
            }
        }

        return $this->heuresEctsUe;
    }
}
