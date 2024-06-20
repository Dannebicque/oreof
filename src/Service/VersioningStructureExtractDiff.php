<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Service/VersioningStructure.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 11/05/2024 07:26
 */

namespace App\Service;

use App\DTO\DiffObject;
use App\DTO\HeuresEctsEc;
use App\DTO\HeuresEctsSemestre;
use App\DTO\HeuresEctsUe;
use App\DTO\StructureEc;
use App\DTO\StructureParcours;
use App\DTO\StructureSemestre;
use App\DTO\StructureUe;
use App\Entity\Mccc;
use App\Utils\Tools;

class VersioningStructureExtractDiff
{
    // On ne sauvegarde dans le tableau que les différences.

    public array $diffUe = [];
    public array $diff = [];
    private bool $hasModification = false;

    public function __construct(
        private StructureParcours $dtoOrigine,
        private StructureParcours $dtoNouveau
    ) {
    }

    //todo: gérer le cas d'ajout d'une UE, voire d'un Semestre
    //todo: gérer le cas d'une suppression EC, UE, Semestre entre ancien et nouveau

    public function extractDiff(): void
    {
        // parcourir les deux structures et comparer. Construire un tableau de différences
        foreach ($this->dtoOrigine->semestres as $ordreSemestre => $semestre) {
            if (array_key_exists($ordreSemestre, $this->dtoNouveau->semestres)) {
                $this->diff['semestres'][$ordreSemestre]['heuresEctsSemestre'] = $this->compareHeuresEctsSemestre($semestre->heuresEctsSemestre, $this->dtoNouveau->semestres[$ordreSemestre]->heuresEctsSemestre);

                $this->compareSemestre($semestre, $this->dtoNouveau->semestres[$ordreSemestre], $ordreSemestre);
            }
        }
        $this->diff['heuresEctsFormation'] = $this->compareHeuresEctsFormation($this->dtoOrigine->heuresEctsFormation, $this->dtoNouveau->heuresEctsFormation);
    }

    private function compareSemestre(
        StructureSemestre $semestreOriginal,
        StructureSemestre $semestreNouveau,
        int $ordreSemestre
    ): void {
        foreach ($semestreOriginal->ues as $ordreUe => $ue) {
            $this->hasModification = false;
            $modifs = $this->compareUe($ue, $semestreNouveau->ues[$ordreUe]);//cas si UE n'existe plus ou si ajouté dans nouveau ?
            if ($this->hasModification === true) {
                $this->diffUe[$ordreSemestre][] =
                    ['ue' => $ue, 'modifications' => $modifs];
            }
        }
    }

    private function compareHeuresEctsSemestre(HeuresEctsSemestre $heuresEctsSemestre, HeuresEctsSemestre $heuresEctsSemestreNouveau): array
    {
        $diff = [];
        $diff['sommeSemestreEcts'] = new DiffObject($heuresEctsSemestre->sommeSemestreEcts, $heuresEctsSemestreNouveau->sommeSemestreEcts);
        $diff['sommeSemestreCmPres'] = new DiffObject(Tools::filtreHeures($heuresEctsSemestre->sommeSemestreCmPres), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreCmPres));
        $diff['sommeSemestreTdPres'] = new DiffObject(Tools::filtreHeures($heuresEctsSemestre->sommeSemestreTdPres), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreTdPres));
        $diff['sommeSemestreTpPres'] = new DiffObject(Tools::filtreHeures($heuresEctsSemestre->sommeSemestreTpPres), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreTpPres));
        $diff['sommeSemestreTePres'] = new DiffObject(Tools::filtreHeures($heuresEctsSemestre->sommeSemestreTePres), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreTePres));
        $diff['sommeSemestreCmDist'] = new DiffObject(Tools::filtreHeures($heuresEctsSemestre->sommeSemestreCmDist), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreCmDist));
        $diff['sommeSemestreTdDist'] = new DiffObject(Tools::filtreHeures($heuresEctsSemestre->sommeSemestreTdDist), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreTdDist));
        $diff['sommeSemestreTpDist'] = new DiffObject(Tools::filtreHeures($heuresEctsSemestre->sommeSemestreTpDist), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreTpDist));

        $sommeSemestreTotalPres = $heuresEctsSemestre->sommeSemestreCmPres + $heuresEctsSemestre->sommeSemestreTdPres + $heuresEctsSemestre->sommeSemestreTpPres;
        $diff['sommeSemestreTotalPres'] = new DiffObject(Tools::filtreHeures($sommeSemestreTotalPres), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreTotalPres()));

        $sommeSemestreTotalDist = $heuresEctsSemestre->sommeSemestreCmDist + $heuresEctsSemestre->sommeSemestreTdDist + $heuresEctsSemestre->sommeSemestreTpDist;
        $diff['sommeSemestreTotalDist'] = new DiffObject(Tools::filtreHeures($sommeSemestreTotalDist), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreTotalDist()));

        $sommeSemestreTotalPresDist = $sommeSemestreTotalPres + $sommeSemestreTotalDist;
        $diff['sommeSemestreTotalPresDist'] = new DiffObject(Tools::filtreHeures($sommeSemestreTotalPresDist), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreTotalPresDist()));

        $this->hasModification = $this->hasModifications($diff);

        return $diff;
    }

    private function compareUe(StructureUe $ueOriginale, ?StructureUe $ueNouvelle): array|false
    {
        $diff = [];

        if ($ueNouvelle !== null) {
            $diff['display'] = new DiffObject($ueOriginale->display, $ueNouvelle->display);
            $diff['libelle'] = new DiffObject($ueOriginale->ue->getLibelle(), $ueNouvelle->ue->getLibelle());
            foreach ($ueOriginale->elementConstitutifs as $ordreEc => $ec) {
                if (!array_key_exists($ordreEc, $ueNouvelle->elementConstitutifs)) {
                    //donc n'existe plus ?
                    $diff['elementConstitutifs'][$ordreEc] = $this->compareElementConstitutif($ec, null);

                } else {
                    $diff['elementConstitutifs'][$ordreEc] = $this->compareElementConstitutif($ec, $ueNouvelle->elementConstitutifs[$ordreEc]);
                }
            }

            foreach ($ueNouvelle->elementConstitutifs as $ordreEc => $ec) {
                if (!array_key_exists($ordreEc, $ueOriginale->elementConstitutifs)) {
                    //donc nouvel EC
                    $diff['elementConstitutifs'][$ordreEc] = $this->compareElementConstitutif(null, $ec);
                }
            }


            foreach ($ueOriginale->uesEnfants() as $ordreUeEnfant => $ueEnfant) {
                if (array_key_exists($ordreUeEnfant, $ueNouvelle->uesEnfants())) {
                    $diff['uesEnfants'][$ordreUeEnfant] = $this->compareUe($ueEnfant, $ueNouvelle->uesEnfants()[$ordreUeEnfant]);
                } else {
                    //donc n'existe plus ?
                }
            }
        }

        $this->hasModification = $this->hasModifications($diff);

        return $this->hasModification ? $diff : false;
    }

    private function compareElementConstitutif(?StructureEc $ecOriginal, ?StructureEc $ecNouveau): array|false
    {
        $diff = [];
        if ($ecOriginal === null && $ecNouveau !== null) {
            if ($ecNouveau->elementConstitutif->getFicheMatiere() !== null) {
                $libelleNew = $ecNouveau->elementConstitutif->getFicheMatiere()->getLibelle();
            } else {
                $libelleNew = $ecNouveau->elementConstitutif->getLibelle();
            }

            $diff['libelle'] = new DiffObject('-', $libelleNew);
            $diff['code'] = new DiffObject('-', $ecNouveau->elementConstitutif->getCode());
            //  $diff['raccroche'] = new DiffObject(null, $ecNouveau->raccroche);
            $diff['heuresEctsEc'] = $this->compareHeuresEctsEc(null, $ecNouveau->heuresEctsEc);
            $diff['typeMccc'] = new DiffObject('', $ecNouveau->typeMccc);
            $diff['mcccs'] = $this->compareMcccs([], $ecNouveau->mcccs);

            // todo: gérer si un EC est ajouté avec des enfants           if ($ecNouveau->elementConstitutif->getNatureUeEc()?->isChoix()) {
            //                //EC enfants
            //                foreach ($ecNouveau->elementsConstitutifsEnfants as $ordreEc => $ecEnfant) {
            //                    if (array_key_exists($ordreEc, $ecOriginal->elementsConstitutifsEnfants)) {
            //                        $diff['ecEnfants'][$ordreEc] = $this->compareElementConstitutif($ecOriginal->elementsConstitutifsEnfants[$ordreEc], $ecEnfant);
            //                    } //else supprimé
            //                }
            //            }

            return $diff;
        }

        if ($ecOriginal !== null && $ecNouveau === null) {
            if ($ecOriginal->elementConstitutif->getFicheMatiere() !== null) {
                $libelleNew = $ecOriginal->elementConstitutif->getFicheMatiere()->getLibelle();
            } else {
                $libelleNew = $ecOriginal->elementConstitutif->getLibelle();
            }

            $diff['libelle'] = new DiffObject($libelleNew, '-');
            $diff['code'] = new DiffObject($ecOriginal->elementConstitutif->getCode(), '');
            //  $diff['raccroche'] = new DiffObject($ecOriginal->raccroche, null);
            $diff['heuresEctsEc'] = $this->compareHeuresEctsEc($ecOriginal->heuresEctsEc, null);
            $diff['typeMccc'] = new DiffObject($ecOriginal->typeMccc, '-');
            $diff['mcccs'] = $this->compareMcccs($ecOriginal->mcccs, []);

            // todo: gérer si un EC est ajouté avec des enfants           if ($ecNouveau->elementConstitutif->getNatureUeEc()?->isChoix()) {
            //                //EC enfants
            //                foreach ($ecNouveau->elementsConstitutifsEnfants as $ordreEc => $ecEnfant) {
            //                    if (array_key_exists($ordreEc, $ecOriginal->elementsConstitutifsEnfants)) {
            //                        $diff['ecEnfants'][$ordreEc] = $this->compareElementConstitutif($ecOriginal->elementsConstitutifsEnfants[$ordreEc], $ecEnfant);
            //                    } //else supprimé
            //                }
            //            }

            return $diff;
        }

        if ($ecOriginal->elementConstitutif->getFicheMatiere() !== null) {
            $libelleOriginal = $ecOriginal->elementConstitutif->getFicheMatiere()->getLibelle();
        } else {
            $libelleOriginal = $ecOriginal->elementConstitutif->getLibelle();
        }

        if ($ecNouveau->elementConstitutif->getFicheMatiere() !== null) {
            $libelleNew = $ecNouveau->elementConstitutif->getFicheMatiere()->getLibelle();
        } else {
            $libelleNew = $ecNouveau->elementConstitutif->getLibelle();
        }

        $diff['libelle'] = new DiffObject($libelleOriginal, $libelleNew);
        $diff['code'] = new DiffObject($ecOriginal->elementConstitutif->getCode(), $ecNouveau->elementConstitutif->getCode());
        // $diff['raccroche'] = new DiffObject($ecOriginal->raccroche, $ecNouveau->raccroche);
        $diff['heuresEctsEc'] = $this->compareHeuresEctsEc($ecOriginal->heuresEctsEc, $ecNouveau->heuresEctsEc);
        if ($ecOriginal->typeMccc !== null && $ecNouveau->typeMccc !== null) {
            $diff['typeMccc'] = new DiffObject($ecOriginal->typeMccc, $ecNouveau->typeMccc);
            $diff['mcccs'] = $this->compareMcccs($ecOriginal->mcccs, $ecNouveau->mcccs);
        }

        if ($ecNouveau->elementConstitutif->getNatureUeEc()?->isChoix()) {
            //EC enfants
            foreach ($ecNouveau->elementsConstitutifsEnfants as $ordreEc => $ecEnfant) {
                if (array_key_exists($ordreEc, $ecOriginal->elementsConstitutifsEnfants)) {
                    $modif = $this->compareElementConstitutif($ecOriginal->elementsConstitutifsEnfants[$ordreEc], $ecEnfant);
                    if ($modif !== null) {
                        $diff['ecEnfants'][$ordreEc] = $modif;
                    }
                } else {
                    //création
                    $diff['ecEnfants'][$ordreEc] = $this->compareElementConstitutif(null, $ecEnfant);
                }
            }
        }

        $this->hasModification = $this->hasModifications($diff);

        return $this->hasModification ? $diff : false;
    }

    private function compareHeuresEctsEc(?HeuresEctsEc $heuresEctsEc, ?HeuresEctsEc $heuresEctsEc1): array|false
    {
        $diff = [];

        if ($heuresEctsEc === null && $heuresEctsEc1 !== null) {
            $diff['ects'] = new DiffObject(0, $heuresEctsEc1->ects);
            $diff['cmPres'] = new DiffObject(0, Tools::filtreHeures($heuresEctsEc1->cmPres));
            $diff['tdPres'] = new DiffObject(0, Tools::filtreHeures($heuresEctsEc1->tdPres));
            $diff['tpPres'] = new DiffObject(0, Tools::filtreHeures($heuresEctsEc1->tpPres));
            $diff['tePres'] = new DiffObject(0, Tools::filtreHeures($heuresEctsEc1->tePres));
            $diff['cmDist'] = new DiffObject(0, Tools::filtreHeures($heuresEctsEc1->cmDist));
            $diff['tdDist'] = new DiffObject(0, Tools::filtreHeures($heuresEctsEc1->tdDist));
            $diff['tpDist'] = new DiffObject(0, Tools::filtreHeures($heuresEctsEc1->tpDist));

            $diff['sommeEcTotalPres'] = new DiffObject(0, Tools::filtreHeures($heuresEctsEc1->sommeEcTotalPres()));
            $diff['sommeEcTotalDist'] = new DiffObject(0, Tools::filtreHeures($heuresEctsEc1->sommeEcTotalDist()));
            $diff['sommeEcTotalPresDist'] = new DiffObject(0, Tools::filtreHeures($heuresEctsEc1->sommeEcTotalPresDist()));
        } elseif ($heuresEctsEc !== null && $heuresEctsEc1 === null) {
            $diff['ects'] = new DiffObject($heuresEctsEc->ects, 0);
            $diff['cmPres'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->cmPres), 0);
            $diff['tdPres'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->tdPres), 0);
            $diff['tpPres'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->tpPres), 0);
            $diff['tePres'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->tePres), 0);
            $diff['cmDist'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->cmDist), 0);
            $diff['tdDist'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->tdDist), 0);
            $diff['tpDist'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->tpDist), 0);


            $diff['sommeEcTotalPres'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->sommeEcTotalPres()), 0);
            $diff['sommeEcTotalDist'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->sommeEcTotalDist()), 0);
            $diff['sommeEcTotalPresDist'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->sommeEcTotalPresDist()), 0);
        } else {
            $diff['ects'] = new DiffObject($heuresEctsEc->ects, $heuresEctsEc1->ects);
            $diff['cmPres'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->cmPres), Tools::filtreHeures($heuresEctsEc1->cmPres));
            $diff['tdPres'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->tdPres), Tools::filtreHeures($heuresEctsEc1->tdPres));
            $diff['tpPres'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->tpPres), Tools::filtreHeures($heuresEctsEc1->tpPres));
            $diff['tePres'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->tePres), Tools::filtreHeures($heuresEctsEc1->tePres));
            $diff['cmDist'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->cmDist), Tools::filtreHeures($heuresEctsEc1->cmDist));
            $diff['tdDist'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->tdDist), Tools::filtreHeures($heuresEctsEc1->tdDist));
            $diff['tpDist'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->tpDist), Tools::filtreHeures($heuresEctsEc1->tpDist));

            $diff['sommeEcTotalPres'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->sommeEcTotalPres()), Tools::filtreHeures($heuresEctsEc1->sommeEcTotalPres()));
            $diff['sommeEcTotalDist'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->sommeEcTotalDist()), Tools::filtreHeures($heuresEctsEc1->sommeEcTotalDist()));
            $diff['sommeEcTotalPresDist'] = new DiffObject(Tools::filtreHeures($heuresEctsEc->sommeEcTotalPresDist()), Tools::filtreHeures($heuresEctsEc1->sommeEcTotalPresDist()));
        }

        $this->hasModification = $this->hasModifications($diff);

        return $this->hasModification ? $diff : false;
    }

    private function compareHeuresEctsFormation(mixed $heuresEctsFormation, $heuresEctsFormationNouveau): array|false
    {
        $diff = [];
        $diff['sommeFormationEcts'] = new DiffObject($heuresEctsFormation->sommeFormationEcts, $heuresEctsFormationNouveau->sommeFormationEcts);
        $diff['sommeFormationCmPres'] = new DiffObject(Tools::filtreHeures($heuresEctsFormation->sommeFormationCmPres), Tools::filtreHeures($heuresEctsFormationNouveau->sommeFormationCmPres));
        $diff['sommeFormationTdPres'] = new DiffObject(Tools::filtreHeures($heuresEctsFormation->sommeFormationTdPres), Tools::filtreHeures($heuresEctsFormationNouveau->sommeFormationTdPres));
        $diff['sommeFormationTpPres'] = new DiffObject(Tools::filtreHeures($heuresEctsFormation->sommeFormationTpPres), Tools::filtreHeures($heuresEctsFormationNouveau->sommeFormationTpPres));
        $diff['sommeFormationTePres'] = new DiffObject(Tools::filtreHeures($heuresEctsFormation->sommeFormationTePres), Tools::filtreHeures($heuresEctsFormationNouveau->sommeFormationTePres));
        $diff['sommeFormationCmDist'] = new DiffObject(Tools::filtreHeures($heuresEctsFormation->sommeFormationCmDist), Tools::filtreHeures($heuresEctsFormationNouveau->sommeFormationCmDist));
        $diff['sommeFormationTdDist'] = new DiffObject(Tools::filtreHeures($heuresEctsFormation->sommeFormationTdDist), Tools::filtreHeures($heuresEctsFormationNouveau->sommeFormationTdDist));
        $diff['sommeFormationTpDist'] = new DiffObject(Tools::filtreHeures($heuresEctsFormation->sommeFormationTpDist), Tools::filtreHeures($heuresEctsFormationNouveau->sommeFormationTpDist));

        $sommeFormationTotalPres = $heuresEctsFormation->sommeFormationCmPres + $heuresEctsFormation->sommeFormationTdPres + $heuresEctsFormation->sommeFormationTpPres;
        $diff['sommeFormationTotalPres'] = new DiffObject(Tools::filtreHeures($sommeFormationTotalPres), Tools::filtreHeures($heuresEctsFormationNouveau->sommeFormationTotalPres()));

        $sommeFormationTotalDist = $heuresEctsFormation->sommeFormationCmDist + $heuresEctsFormation->sommeFormationTdDist + $heuresEctsFormation->sommeFormationTpDist;
        $diff['sommeFormationTotalDist'] = new DiffObject(Tools::filtreHeures($sommeFormationTotalDist), Tools::filtreHeures($heuresEctsFormationNouveau->sommeFormationTotalDist()));

        $sommeFormationTotalPresDist = $sommeFormationTotalDist + $sommeFormationTotalPres;
        $diff['sommeFormationTotalPresDist'] = new DiffObject(Tools::filtreHeures($sommeFormationTotalPresDist), Tools::filtreHeures($heuresEctsFormationNouveau->sommeFormationTotalPresDist()));

        return $diff;
    }

    private function compareMcccs(?array $mcccsOriginal, ?array $mcccsNouveau): array|false
    {
        if (null === $mcccsOriginal && null === $mcccsNouveau) {
            return [];
        }

        $diff = [];

        foreach ($mcccsNouveau as $mcccNouveau) {
            $diff['new'][$mcccNouveau->getId()] = $mcccNouveau;
        }

        foreach ($mcccsOriginal as $mcccOriginal) {
            $diff['original'][$mcccOriginal['id']] = $this->createMcccFromArray($mcccOriginal);
        }


        $this->hasModification = $this->hasModifications($diff);

        return $this->hasModification ? $diff : false;
    }

    private function createMcccFromArray(array $mcccOriginal): Mccc
    {
        $mccc = new Mccc();
        $mccc->setId($mcccOriginal['id']);
        if (array_key_exists('duree', $mcccOriginal)) {
            // création d'un objet DateTime à partir d'une chaine de caractères
            $mccc->setDuree(new \DateTime($mcccOriginal['duree']));
        }
        $mccc->setLibelle($mcccOriginal['libelle']);
        $mccc->setNumeroSession($mcccOriginal['numeroSession']);
        $mccc->setSecondeChance($mcccOriginal['secondeChance'] == 1);
        $mccc->setPourcentage($mcccOriginal['pourcentage']);
        $mccc->setNbEpreuves($mcccOriginal['nbEpreuves']);
        $mccc->setTypeEpreuve($mcccOriginal['typeEpreuve']);
        $mccc->setControleContinu($mcccOriginal['controleContinu'] == 1);
        $mccc->setExamenTerminal($mcccOriginal['examenTerminal'] == 1);
        if (array_key_exists('numeroEpreuve', $mcccOriginal)) {
            // création d'un objet DateTime à partir d'une chaine de caractères
            $mccc->setNumeroEpreuve($mcccOriginal['numeroEpreuve']);
        }


        if (array_key_exists('options', $mcccOriginal)) {
            $mccc->setOptions($mcccOriginal['options']);
        }

        return $mccc;
    }

    private function hasModifications(array $diff): bool
    {
        foreach ($diff as $key => $value) {
            if (is_array($value)) {
                if ($this->hasModifications($value)) {
                    return true;
                }
            }

            if ($value instanceof DiffObject && $value->isDifferent()) {
                 return true;
            }
        }

        return false;
    }
}
