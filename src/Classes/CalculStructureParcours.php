<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/CalculStructureParcours.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/07/2023 18:50
 */

namespace App\Classes;

use App\DTO\HeuresEctsFormation;
use App\DTO\HeuresEctsSemestre;
use App\DTO\HeuresEctsUe;

class CalculStructureParcours
{
    public function calcul(\App\Entity\Parcours $parcours): HeuresEctsFormation
    {
        $dto = new HeuresEctsFormation();

        foreach ($parcours->getSemestreParcours() as $semestreParcours) {
            //todo: vérifier si raccrochée ou non pour prendre les bonnes UES
            $dtoSemestre = new HeuresEctsSemestre();
            foreach ($semestreParcours->getSemestre()->getUes() as $ue) {
                if ($ue->getUeRaccrochee() !== null) {
                    $ue = $ue->getUeRaccrochee()->getUe();
                }
                if ($ue->getUeParent() === null) {
                    $dtoUe = new HeuresEctsUe();//todo: les ECTS peuvent être sur l'UE
                    foreach ($ue->getElementConstitutifs() as $elementConstitutif) {
                        $dtoUe->addEc($elementConstitutif); //todo: les gérer les enfants et les heures communes ou pas
                    }
                    $dtoSemestre->addUe($ue->getId(), $dtoUe);

                    foreach ($ue->getUeEnfants() as $ueEnfant) {
                        if ($ueEnfant->getUeRaccrochee() !== null) {
                            $ueEnfant = $ueEnfant->getUeRaccrochee()->getUe();
                        }
                        $dtoUeEnfant = new HeuresEctsUe();
                        foreach ($ueEnfant->getElementConstitutifs() as $elementConstitutif) {
                            $dtoUeEnfant->addEc($elementConstitutif);
                        }
                        $dtoSemestre->addUe($ueEnfant->getId(), $dtoUeEnfant);
                    }

                }
            }
            $dto->addSemestre($semestreParcours->getId(), $dtoSemestre);
        }

        return $dto;
    }
}
