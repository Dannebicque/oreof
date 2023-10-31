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
use App\DTO\StructureEc;
use App\DTO\StructureParcours;
use App\DTO\StructureSemestre;
use App\DTO\StructureUe;
use App\Entity\Parcours;

class CalculStructureParcours
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

            if ($semestre !== null) {
                $dtoSemestre = new StructureSemestre($semestre, $semestreParcours->getOrdre(), $raccrocheSemestre);

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

                        //si des UE enfants, on ne regarde pas s'il y a des EC
                        $dtoUe = new StructureUe($ue, $raccrocheUe, $display, $ueOrigine ?? null);
                        foreach ($ue->getElementConstitutifs() as $elementConstitutif) {
                            if ($elementConstitutif !== null && $elementConstitutif->getEcParent() === null) {
                                //récupérer le bon EC selon tous les liens
                                $dtoEc = new StructureEc($elementConstitutif, $parcours);
                                foreach ($elementConstitutif->getEcEnfants() as $elementConstitutifEnfant) {
                                    $dtoEcEnfant = new StructureEc($elementConstitutifEnfant, $parcours);
                                    $dtoEc->addEcEnfant($elementConstitutifEnfant->getId(), $dtoEcEnfant);
                                }
                                $dtoUe->addEc($dtoEc);
                            }
                        }

                        foreach ($ue->getUeEnfants() as $ueEnfant) {
                            $display = $ueEnfant->display($parcours);
                            if ($ueEnfant !== null && $ueEnfant->getUeRaccrochee() !== null) {
                                $ueOrigine = $ueEnfant;
                                $ueEnfant = $ueEnfant->getUeRaccrochee()->getUe();
                                $raccrocheUeEnfant = true;
                            } else {
                                $raccrocheUeEnfant = $raccrocheUe;
                            }

                            if ($ueEnfant !== null) {
                                $dtoUeEnfant = new StructureUe($ueEnfant, $raccrocheUeEnfant, $display, $ueOrigine ?? null);
                                foreach ($ueEnfant->getElementConstitutifs() as $elementConstitutif) {
                                    if ($elementConstitutif !== null && $elementConstitutif->getEcParent() === null) {
                                        $dtoEc = new StructureEc($elementConstitutif, $parcours);

                                        foreach ($elementConstitutif->getEcEnfants() as $elementConstitutifEnfant) {
                                            $dtoEcEnfant = new StructureEc($elementConstitutifEnfant, $parcours);
                                            $dtoEc->addEcEnfant($elementConstitutifEnfant->getId(), $dtoEcEnfant);
                                        }
                                        $dtoUeEnfant->addEc($dtoEc);
                                    }
                                }
                                $dtoUe->addUeEnfant($ueEnfant->getId(), $dtoUeEnfant);
                            }
                        }
                        $dtoSemestre->addUe($ue->getId(), $dtoUe);
                    }
                }
                $dtoStructure->addSemestre($semestreParcours->getOrdre(), $dtoSemestre);
            }
        }

        return $dtoStructure;
    }

    public function calculVersioning(Parcours $parcours): StructureParcours
    {
        $dtoStructure = new StructureParcours();
        $dtoStructure->setParcours($parcours);

        $indexSemestre = 0;
        $indexUe = 0;
        $indexUeEnfant = 0;
        $indexEc = 0;
        $indexEcEnfant = 0;
        $indexEcUeEnfant = 0;
        $indexEcEnfantFromEcEnfant = 0;
        $indexEcFromUeEnfant = 0;

        foreach ($parcours->getSemestreParcours() as $semestreParcours) {
            $indexSemestre++;
            if ($semestreParcours->getSemestre()?->getSemestreRaccroche() !== null) {
                $semestre = $semestreParcours->getSemestre()?->getSemestreRaccroche()?->getSemestre();
                $raccrocheSemestre = true;
            } else {
                $semestre = $semestreParcours->getSemestre();
                $raccrocheSemestre = false;
            }

            if ($semestre !== null) {
                $dtoSemestre = new StructureSemestre($semestre, $semestreParcours->getOrdre(), $raccrocheSemestre);

                foreach ($semestre->getUes() as $ue) {
                    if($ue){
                        $indexUe++;
                        if ($ue !== null && $ue->getUeParent() === null) {
                            $display = $ue->display($parcours);
                            if ($ue->getUeRaccrochee() !== null) {
                                $ueOrigine = $ue;
                                $ue = $ue->getUeRaccrochee()->getUe();
                                $raccrocheUe = true;
                            } else {
                                $raccrocheUe = $raccrocheSemestre;
                            }

                            //si des UE enfants, on ne regarde pas s'il y a des EC
                            $dtoUe = new StructureUe($ue, $raccrocheUe, $display, $ueOrigine ?? null);
                            foreach ($ue->getElementConstitutifs() as $elementConstitutif) {
                                $indexEc++;
                                if ($elementConstitutif !== null && $elementConstitutif->getEcParent() === null) {
                                    //récupérer le bon EC selon tous les liens
                                    $dtoEc = new StructureEc($elementConstitutif, $parcours);
                                    foreach ($elementConstitutif->getEcEnfants() as $elementConstitutifEnfant) {
                                        $indexEcEnfant++;
                                        $dtoEcEnfant = new StructureEc($elementConstitutifEnfant, $parcours);
                                        $dtoEc->addEcEnfant($indexEcEnfant, $dtoEcEnfant);
                                    }
                                    $dtoUe->addEc($dtoEc);
                                }
                            }

                            foreach ($ue->getUeEnfants() as $ueEnfant) {
                                $indexUeEnfant++;
                                $display = $ueEnfant->display($parcours);
                                if ($ueEnfant !== null && $ueEnfant->getUeRaccrochee() !== null) {
                                    $ueOrigine = $ueEnfant;
                                    $ueEnfant = $ueEnfant->getUeRaccrochee()->getUe();
                                    $raccrocheUeEnfant = true;
                                } else {
                                    $raccrocheUeEnfant = $raccrocheUe;
                                }

                                if ($ueEnfant !== null) {
                                    $dtoUeEnfant = new StructureUe($ueEnfant, $raccrocheUeEnfant, $display, $ueOrigine ?? null);
                                    foreach ($ueEnfant->getElementConstitutifs() as $elementConstitutif) {
                                        $indexEcFromUeEnfant++;
                                        if ($elementConstitutif !== null && $elementConstitutif->getEcParent() === null) {
                                            $dtoEc = new StructureEc($elementConstitutif, $parcours);

                                            foreach ($elementConstitutif->getEcEnfants() as $elementConstitutifEnfant) {
                                                $indexEcEnfantFromEcEnfant++;
                                                $dtoEcEnfant = new StructureEc($elementConstitutifEnfant, $parcours);
                                                $dtoEc->addEcEnfant($indexEcEnfantFromEcEnfant, $dtoEcEnfant);
                                            }
                                            $dtoUeEnfant->addEc($dtoEc);
                                        }
                                    }
                                    $dtoUe->addUeEnfant($indexUeEnfant, $dtoUeEnfant);
                                }
                            }
                            $dtoSemestre->addUe($indexUe, $dtoUe);
                        }
                    }
                }
                $dtoStructure->addSemestre($semestreParcours->getOrdre(), $dtoSemestre);
            }
        }

        return $dtoStructure;
    }
}

//     public function calculVersioning(Parcours $parcours) {
//         $dtoStructure = new StructureParcours();
//         $dtoStructure->setParcours($parcours);

//         foreach($parcours->getSemestreParcours() as $semestreParcours){
//             $semestre = $semestreParcours->getSemestre();
//             $dtoSemestre = new StructureSemestre($semestre, $semestre->getOrdre());
//             foreach($semestre->getUes() as $ue){
//                 $display = $ue->display($parcours);
//                 $dtoUe = new StructureUe($ue, display: $display);
//                 foreach($ue->getElementConstitutifs() as $ec){
//                     $dtoEc = new StructureEc($ec, $parcours);
//                     foreach ($ec->getEcEnfants() as $ecEnfant) {
//                         $dtoEcEnfant = new StructureEc($ecEnfant, $parcours);
//                         $dtoEc->addEcEnfant($elementConstitutifEnfant->getId(), $dtoEcEnfant);
//                     }
//                 }
//             }
//         }

//         return $dtoStructure;
    
//     }
// }