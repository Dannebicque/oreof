<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/CalculStructureParcours.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 18/10/2023 13:19
 */

namespace App\Classes;

use App\DTO\StructureEc;
use App\DTO\StructureParcours;
use App\DTO\StructureSemestre;
use App\DTO\StructureUe;
use App\Entity\Parcours;

class CalculButStructureParcours
{
    public function calcul(Parcours $parcours): StructureParcours
    {
        $dtoStructure = new StructureParcours();
        $dtoStructure->setParcours($parcours);

        foreach ($parcours->getSemestreParcours() as $semestreParcours) {
            if ($semestreParcours->getSemestre()?->getSemestreRaccroche() !== null) {
                $semestre = $semestreParcours->getSemestre()?->getSemestreRaccroche()?->getSemestre();
                $raccrocheSemestre = true;
            } else {
                $semestre = $semestreParcours->getSemestre();
                $raccrocheSemestre = false;
            }

            if ($semestre !== null && $semestre->isNonDispense() === false) {
                $dtoSemestre = new StructureSemestre($semestre, $semestreParcours->getOrdre(), $raccrocheSemestre, $semestreParcours);

                foreach ($semestre->getUes() as $ue) {
                    if ($ue !== null && $ue->getUeParent() === null) {
                        $display = $ue->display($parcours);
                        if ($ue->getUeRaccrochee() !== null) {
                            $ueOrigine = $ue;
                            $ue = $ue->getUeRaccrochee()->getUe();
                            $raccrocheUe = true;
                        } else {
                            $raccrocheUe = $raccrocheSemestre;
                        }

                        $dtoUe = new StructureUe($ue, $raccrocheUe, $display, $ueOrigine ?? null);
                        foreach ($ue->getElementConstitutifs() as $elementConstitutif) {
                            if ($elementConstitutif !== null && $elementConstitutif->getEcParent() === null) {
                                //récupérer le bon EC selon tous les liens
                                $dtoEc = new StructureEc($elementConstitutif, $parcours, true);
                                foreach ($elementConstitutif->getEcEnfants() as $elementConstitutifEnfant) {
                                    $dtoEcEnfant = new StructureEc($elementConstitutifEnfant, $parcours, true);
                                    $dtoEc->addEcEnfant($elementConstitutifEnfant->getId(), $dtoEcEnfant);
                                }
                                $dtoUe->addEc($dtoEc);
                            }

                        }
                        $dtoUe->heuresEctsUe->sommeUeEcts = $ue->getEcts();
                        $dtoSemestre->addUe($ue->getOrdre(), $dtoUe);//todo: utilisation de l'ordre de l'ue plutôt que l'id pour la comparaison
                    }
                }
                $dtoStructure->addSemestre($semestreParcours->getOrdre(), $dtoSemestre);
            }
        }

        return $dtoStructure;
    }
}
