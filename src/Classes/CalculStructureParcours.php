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
use App\DTO\StructureEcVersioning;
use App\DTO\StructureParcours;
use App\DTO\StructureSemestre;
use App\DTO\StructureUe;
use App\Entity\Parcours;
use App\Repository\ElementConstitutifRepository;
use App\Repository\ParcoursRepository;
use App\Repository\UeRepository;
use Doctrine\ORM\EntityManagerInterface;

class CalculStructureParcours
{
    public function __construct(

        protected EntityManagerInterface $entityManager,
        protected ElementConstitutifRepository $elementConstitutifRepository,
        protected UeRepository $ueRepository,
        protected ?ParcoursRepository $parcoursRepository = null,
    )
    {
    }

    public function calcul(Parcours $parcours, bool $withEcts = true, bool $withBcc = true): StructureParcours
    {
        if ($this->parcoursRepository !== null) {
            $parcours = $this->parcoursRepository->find($parcours->getId());
        }

        $dtoStructure = new StructureParcours($withEcts, $withBcc);
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
                $dtoSemestre = new StructureSemestre($semestre, $semestreParcours->getOrdre(), $raccrocheSemestre, $semestreParcours, $withEcts, $withBcc);
                $ues = $this->ueRepository->getBySemestre($semestre);
                foreach ($ues as $ue) {
                    if ($ue !== null && $ue->getUeParent() === null) {
                        $display = $ue->display($parcours);
                        $ueOrigine = $ue;
                        if ($ue->getUeRaccrochee() !== null) {
                            $ue = $ue->getUeRaccrochee()->getUe();
                            $raccrocheUe = true;
                        } else {
                            $raccrocheUe = $raccrocheSemestre;
                        }

                        //si des UE enfants, on ne regarde pas s'il y a des EC
                        $dtoUe = new StructureUe($ue, $raccrocheUe, $display, $ueOrigine ?? null, $withEcts, $withBcc);
                        $ecs = $this->elementConstitutifRepository->getByUe($ue);

                        foreach ($ecs as $elementConstitutif) {
                            if ($elementConstitutif !== null && $elementConstitutif->getEcParent() === null) {
                                //récupérer le bon EC selon tous les liens
                                $dtoEc = new StructureEc($elementConstitutif, $parcours, false, $withEcts, $withBcc);
                                $dtoStructure->statsFichesMatieresParcours->addEc($elementConstitutif, $raccrocheUe);
                                foreach ($elementConstitutif->getEcEnfants() as $elementConstitutifEnfant) {
                                    $dtoEcEnfant = new StructureEc($elementConstitutifEnfant, $parcours, false, $withEcts, $withBcc);
                                    $dtoStructure->statsFichesMatieresParcours->addEc($elementConstitutifEnfant, $raccrocheUe);
                                    $dtoEc->addEcEnfant($elementConstitutifEnfant->getId(), $dtoEcEnfant);
                                }
                                $dtoUe->addEc($dtoEc);
                            }
                        }

                        foreach ($ue->getUeEnfants() as $ueEnfant) {
                            $display = $ueEnfant->display($parcours);
                            $ueOrigineEnfant = $ueEnfant;
                            if ($ueEnfant !== null && $ueEnfant->getUeRaccrochee() !== null) {
                                $ueEnfant = $ueEnfant->getUeRaccrochee()->getUe();
                                $raccrocheUeEnfant = true;
                            } else {
                                $raccrocheUeEnfant = $raccrocheUe;
                            }

                            if ($ueEnfant !== null) {
                                $dtoUeEnfant = new StructureUe($ueEnfant, $raccrocheUeEnfant, $display, $ueOrigineEnfant ?? null, $withEcts, $withBcc);
                                $ecsUeEnfant = $this->elementConstitutifRepository->getByUe($ueEnfant);
                                foreach ($ecsUeEnfant as $elementConstitutif) {
                                    if ($elementConstitutif !== null && $elementConstitutif->getEcParent() === null) {
                                        $dtoEc = new StructureEc($elementConstitutif, $parcours, false, $withEcts, $withBcc);
                                        $dtoStructure->statsFichesMatieresParcours->addEc($elementConstitutif, $raccrocheUeEnfant);
                                        foreach ($elementConstitutif->getEcEnfants() as $elementConstitutifEnfant) {
                                            $dtoEcEnfant = new StructureEc($elementConstitutifEnfant, $parcours, false, $withEcts, $withBcc);
                                            $dtoStructure->statsFichesMatieresParcours->addEc($elementConstitutifEnfant, $raccrocheUeEnfant);
                                            $dtoEc->addEcEnfant($elementConstitutifEnfant->getId(), $dtoEcEnfant);
                                        }
                                        $dtoUeEnfant->addEc($dtoEc);
                                    }
                                }

                                // -------------------------------
                                // Deuxieme niveau d'UE enfant $ueEnfant
                                // -------------------------------
                                foreach ($ueEnfant->getUeEnfants() as $ueEnfant2) {
                                    $display2 = $ueEnfant2->display($parcours, subsubniveau: true, subniveau: $display);
                                    $ueOrigineEnfant2 = $ueEnfant2;
                                    if ($ueEnfant2 !== null && $ueEnfant2->getUeRaccrochee() !== null) {
                                        $ueEnfant2 = $ueEnfant2->getUeRaccrochee()->getUe();
                                        $raccrocheUeEnfant2 = true;
                                    } else {
                                        $raccrocheUeEnfant2 = $raccrocheUeEnfant;
                                    }

                                    if ($ueEnfant2 !== null) {
                                        $dtoUeEnfant2 = new StructureUe($ueEnfant2, $raccrocheUeEnfant2, $display2, $ueOrigineEnfant2 ?? null, $withEcts, $withBcc);
                                        $ecsUeEnfant2 = $this->elementConstitutifRepository->getByUe($ueEnfant2);
                                        foreach ($ecsUeEnfant2 as $elementConstitutif) {
                                            if ($elementConstitutif !== null && $elementConstitutif->getEcParent() === null) {
                                                $dtoEc = new StructureEc($elementConstitutif, $parcours, false, $withEcts, $withBcc);
                                                $dtoStructure->statsFichesMatieresParcours->addEc($elementConstitutif, $raccrocheUeEnfant2);
                                                foreach ($elementConstitutif->getEcEnfants() as $elementConstitutifEnfant) {
                                                    $dtoEcEnfant = new StructureEc($elementConstitutifEnfant, $parcours, false, $withEcts, $withBcc);
                                                    $dtoStructure->statsFichesMatieresParcours->addEc($elementConstitutifEnfant, $raccrocheUeEnfant2);
                                                    $dtoEc->addEcEnfant($elementConstitutifEnfant->getId(), $dtoEcEnfant);
                                                }
                                                $dtoUeEnfant2->addEc($dtoEc);
                                            }
                                        }
                                        $dtoUeEnfant->addUeEnfant($ueOrigineEnfant2->getOrdre(), $dtoUeEnfant2);
                                    }
                                }

                                $dtoUe->addUeEnfant($ueOrigineEnfant->getOrdre(), $dtoUeEnfant);
                            }
                        }
                        $dtoSemestre->addUe($ueOrigine->getOrdre(), $dtoUe);
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
                        $ueOrigine = $ue;
                        if ($ue->getUeRaccrochee() !== null) {

                            $ue = $ue->getUeRaccrochee()->getUe();
                            $raccrocheUe = true;
                        } else {
                            $raccrocheUe = $raccrocheSemestre;
                        }

                        //si des UE enfants, on ne regarde pas s'il y a des EC
                        $dtoUe = new StructureUe($ue, $raccrocheUe, $display, $ueOrigine ?? null);
                       // $ecs = $this->elementConstitutifRepository->getByUe($ue);

                        foreach ($ue->getElementConstitutifs() as $elementConstitutif) {
                            if ($elementConstitutif !== null && $elementConstitutif->getEcParent() === null) {
                                //récupérer le bon EC selon tous les liens
                                $dtoEc = new StructureEcVersioning($elementConstitutif, $parcours);
                                foreach ($elementConstitutif->getEcEnfants() as $elementConstitutifEnfant) {
                                    $dtoEcEnfant = new StructureEcVersioning($elementConstitutifEnfant, $parcours);
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
                                //$ecsEnfant = $this->elementConstitutifRepository->getByUe($ueEnfant);

                                foreach ($ueEnfant->getElementConstitutifs() as $elementConstitutif) {
                                    if ($elementConstitutif !== null && $elementConstitutif->getEcParent() === null) {
                                        $dtoEc = new StructureEcVersioning($elementConstitutif, $parcours);

                                        foreach ($elementConstitutif->getEcEnfants() as $elementConstitutifEnfant) {
                                            $dtoEcEnfant = new StructureEcVersioning($elementConstitutifEnfant, $parcours);
                                            $dtoEc->addEcEnfant($elementConstitutifEnfant->getId(), $dtoEcEnfant);
                                        }
                                        $dtoUeEnfant->addEc($dtoEc);
                                    }
                                }
                                $dtoUe->addUeEnfant($ueEnfant->getOrdre(), $dtoUeEnfant);
                            }
                        }
                        $dtoSemestre->addUe($ueOrigine->getOrdre(), $dtoUe);
                    }
                }
                $dtoStructure->addSemestre($semestreParcours->getOrdre(), $dtoSemestre);
            }
        }

        return $dtoStructure;
    }

}
