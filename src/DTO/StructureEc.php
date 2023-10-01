<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/StructureEc.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 01/10/2023 10:39
 */

namespace App\DTO;

use App\Entity\ElementConstitutif;

class StructureEc
{
    public ElementConstitutif $elementConstitutif;
    public bool $raccroche = false;
    public array $elementsConstitutifsEnfants = [];
    public HeuresEctsEc $heuresEctsEc;
    public array $heuresEctsEcEnfants = [];


    public function __construct(ElementConstitutif $elementConstitutif, bool $raccroche = false)
    {
        $this->elementConstitutif = $elementConstitutif;
        $this->raccroche = $raccroche;
        $this->heuresEctsEc = new HeuresEctsEc();
        $this->heuresEctsEc->addEc($elementConstitutif);
    }

    public function addEcEnfant(?int $idEc, StructureEc $structureEc): void
    {
        $this->elementsConstitutifsEnfants[$idEc] = $structureEc;
        //gérer pour prendre le max des heures et ects sur tous les enfants de l'EC
    }

    public function getHeuresEctsEc(): HeuresEctsEc
    {
        if (count($this->heuresEctsEcEnfants) > 0) {
            //parcourir le tableau, comparer les objets et retourner celui dont la somme des heures est la plus grande
            foreach ($this->heuresEctsEcEnfants as $heuresEctsEcEnfant) {
                if ($heuresEctsEcEnfant->sommeEcTotalPresDist() > $this->heuresEctsEc->sommeEcTotalPresDist()) {
                    $this->heuresEctsEc = $heuresEctsEcEnfant;
                }
            }
        }

        return $this->heuresEctsEc;
    }
}
