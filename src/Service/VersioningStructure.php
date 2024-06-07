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

class VersioningStructure
{
    public function __construct(
        private StructureParcours $dtoOrigine,
        private StructureParcours $dtoNouveau
    ) {
//        dd($this->dtoOrigine);
    }

    public function calculDiff()
    {
        // parcourir les deux structures et comparer. Construire un tableau de différences
        $diff = [];

        foreach ($this->dtoOrigine->semestres as $ordreSemestre => $semestre) {
            $diff['semestres'][$ordreSemestre] = $this->compareSemestre($semestre, $this->dtoNouveau->semestres[$ordreSemestre]);
        }
        $diff['heuresEctsFormation'] = $this->compareHeuresEctsFormation($this->dtoOrigine->heuresEctsFormation, $this->dtoNouveau->heuresEctsFormation);

        return $diff;
    }

    private function compareSemestre(StructureSemestre $semestreOriginal, StructureSemestre $semestreNouveau)
    {
        $diff = [];

        $diff['raccroche'] = new DiffObject($semestreOriginal->raccroche, $semestreNouveau->raccroche);
        $diff['ordre'] = new DiffObject($semestreOriginal->ordre, $semestreNouveau->ordre);
        $diff['heuresEctsSemestre'] = $this->compareHeuresEctsSemestre($semestreOriginal->heuresEctsSemestre, $semestreNouveau->heuresEctsSemestre);
        foreach ($semestreOriginal->ues as $ordreUe => $ue) {
            $diff['ues'][$ordreUe] = $this->compareUe($ue, $semestreNouveau->ues[$ordreUe]);//cas si UE n'existe plus ou si ajouté dans nouveau ?
        }

        return $diff;
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

        return $diff;
    }

    private function compareUe(StructureUe $ueOriginale, StructureUe $ueNouvelle): array
    {
        /*
         * "display" => "UE 1.1"
          "ueOrigine" => array:4 [▶]
          "ue" => array:4 [▶]
          "raccroche" => false
          "elementConstitutifs" => array:1 [▶]
          "uesEnfants" => []
          "heuresEctsUeEnfants" => []
          "heuresEctsUe" => array:8 [▼
            "sommeUeEcts" => 6.0
            "sommeUeCmPres" => 20.0
            "sommeUeTdPres" => 20.0
            "sommeUeTpPres" => 0.0
            "sommeUeTePres" => 0.0
            "sommeUeCmDist" => 0.0
            "sommeUeTdDist" => 0.0
            "sommeUeTpDist" => 0.0
          ]
         */
        $diff = [];

        $diff['display'] = new DiffObject($ueOriginale->display, $ueNouvelle->display);
        $diff['libelle'] = new DiffObject($ueOriginale->ue->getLibelle(), $ueNouvelle->ue->getLibelle());
        $diff['raccroche'] = new DiffObject($ueOriginale->raccroche, $ueNouvelle->raccroche);
        foreach ($ueOriginale->elementConstitutifs as $ordreEc => $ec) {
            if (!array_key_exists($ordreEc, $ueNouvelle->elementConstitutifs)) {
                //donc n'existe plus ?
            } else {
                $diff['elementConstitutifs'][$ordreEc] = $this->compareElementConstitutif($ec, $ueNouvelle->elementConstitutifs[$ordreEc]);
            }
        }
//dump($ueOriginale);
        foreach ($ueOriginale->uesEnfants() as $ordreUeEnfant => $ueEnfant) {
            if (array_key_exists($ordreUeEnfant, $ueNouvelle->uesEnfants())) {
                $diff['uesEnfants'][$ordreUeEnfant] = $this->compareUe($ueEnfant, $ueNouvelle->uesEnfants()[$ordreUeEnfant]);
            } else {
                //donc n'existe plus ?
            }
        }

        $diff['heuresEctsUe'] = $this->compareHeuresEctsUe($ueOriginale->heuresEctsUe, $ueNouvelle->heuresEctsUe);

        return $diff;
    }

    private function compareElementConstitutif(StructureEc $ecOriginal, StructureEc $ecNouveau): array
    {
        $diff = [];

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
        $diff['raccroche'] = new DiffObject($ecOriginal->raccroche, $ecNouveau->raccroche);
        $diff['heuresEctsEc'] = $this->compareHeuresEctsEc($ecOriginal->heuresEctsEc, $ecNouveau->heuresEctsEc);
        if ($ecOriginal->typeMccc !== null && $ecNouveau->typeMccc !== null) {
            $diff['typeMccc'] = new DiffObject($ecOriginal->typeMccc, $ecNouveau->typeMccc);
            $diff['mcccs'] = $this->compareMcccs($ecOriginal->mcccs, $ecNouveau->mcccs);
        }



        return $diff;
    }

    private function compareHeuresEctsUe(HeuresEctsUe $heuresEctsUe, HeuresEctsUe $heuresEctsUe1)
    {
        $diff = [];
        $diff['sommeUeEcts'] = new DiffObject($heuresEctsUe->sommeUeEcts, $heuresEctsUe1->sommeUeEcts);
        $diff['sommeUeCmPres'] = new DiffObject(Tools::filtreHeures($heuresEctsUe->sommeUeCmPres), Tools::filtreHeures($heuresEctsUe1->sommeUeCmPres));
        $diff['sommeUeTdPres'] = new DiffObject(Tools::filtreHeures($heuresEctsUe->sommeUeTdPres), Tools::filtreHeures($heuresEctsUe1->sommeUeTdPres));
        $diff['sommeUeTpPres'] = new DiffObject(Tools::filtreHeures($heuresEctsUe->sommeUeTpPres), Tools::filtreHeures($heuresEctsUe1->sommeUeTpPres));
        $diff['sommeUeTePres'] = new DiffObject(Tools::filtreHeures($heuresEctsUe->sommeUeTePres), Tools::filtreHeures($heuresEctsUe1->sommeUeTePres));
        $diff['sommeUeCmDist'] = new DiffObject(Tools::filtreHeures($heuresEctsUe->sommeUeCmDist), Tools::filtreHeures($heuresEctsUe1->sommeUeCmDist));
        $diff['sommeUeTdDist'] = new DiffObject(Tools::filtreHeures($heuresEctsUe->sommeUeTdDist), Tools::filtreHeures($heuresEctsUe1->sommeUeTdDist));
        $diff['sommeUeTpDist'] = new DiffObject(Tools::filtreHeures($heuresEctsUe->sommeUeTpDist), Tools::filtreHeures($heuresEctsUe1->sommeUeTpDist));

        $sommeUeTotalPres = $heuresEctsUe->sommeUeCmPres + $heuresEctsUe->sommeUeTdPres + $heuresEctsUe->sommeUeTpPres;
        $diff['sommeUeTotalPres'] = new DiffObject(Tools::filtreHeures($sommeUeTotalPres), Tools::filtreHeures($heuresEctsUe1->sommeUeTotalPres()));

        $sommeUeTotalDist = $heuresEctsUe->sommeUeCmDist + $heuresEctsUe->sommeUeTdDist + $heuresEctsUe->sommeUeTpDist;
        $diff['sommeUeTotalDist'] = new DiffObject(Tools::filtreHeures($sommeUeTotalDist), Tools::filtreHeures($heuresEctsUe1->sommeUeTotalDist()));

        $sommeUeTotalPresDist = $sommeUeTotalPres + $sommeUeTotalDist;
        $diff['sommeUeTotalPresDist'] = new DiffObject(Tools::filtreHeures($sommeUeTotalPresDist), Tools::filtreHeures($heuresEctsUe1->sommeUeTotalPresDist()));

        return $diff;
    }

    private function compareHeuresEctsEc(HeuresEctsEc $heuresEctsEc, HeuresEctsEc $heuresEctsEc1)
    {
        $diff = [];
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

        return $diff;

    }

    private function compareHeuresEctsFormation(mixed $heuresEctsFormation, $heuresEctsFormationNouveau)
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

    public function mapStructureForComparison(StructureParcours $dto)
    {
        $structure = ['semestres' => []];
        // Semestres
        foreach($dto->semestres as $indexS => $semestre) {
            $structure['semestres'][$indexS] = ['idSemestre' => $semestre->semestre->getId(), 'ues' => []];
            $ueArray = $semestre->ues;
            $structure['semestres'][$indexS]['ues'] = $this->mapUeArrayForComparison($ueArray);
            foreach($ueArray as $indexUe => $ue) {
                $ecArray = $ue->elementConstitutifs;
                $structure['semestres'][$indexS]['ues'][$indexUe]['listeEc'] = $this->mapEcArrayForComparison($ecArray);
                foreach($ecArray as $indexEc => $ec) {
                    $ecEnfantData = $ec->elementsConstitutifsEnfants;
                    $structure['semestres'][$indexS]['ues'][$indexUe]['listeEc'][$indexEc]['listeEcsEnfants'] = $this->mapEcArrayForComparison($ecEnfantData);
                }
                $ueEnfantArrayData = $ue->uesEnfants();
                $structure['semestres'][$indexS]['ues'][$indexUe]['uesEnfants'] = $this->mapUeArrayForComparison($ueEnfantArrayData);
                foreach($ueEnfantArrayData as $indexUeEnfant => $ueEnfant) {
                    $ecArrayData = $ueEnfant->elementConstitutifs;
                    $structure['semestres'][$indexS]['ues'][$indexUe]['uesEnfants'][$indexUeEnfant]['listeEc'] = $this->mapEcArrayForComparison($ecArrayData);
                    foreach($ecArrayData as $indexEcEnfantUeEnfant => $ec) {
                        $ecEnfantData = $ec->elementsConstitutifsEnfants;
                        $structure['semestres'][$indexS]['ues'][$indexUe]['uesEnfants'][$indexUeEnfant]['listeEc'][$indexEcEnfantUeEnfant]['listeEcsEnfants'] = $this->mapEcArrayForComparison($ecEnfantData);
                    }
                }
            }
        }

        return $structure;
    }

    public function mapUeArrayForComparison(array $ueArray)
    {
        return array_values(
            array_map(
                fn ($ue) => ["idUe" => $ue->ue->getId()],
                $ueArray
            )
        );
    }

    public function mapEcArrayForComparison(array $ecArray)
    {
        return array_values(
            array_map(
                fn ($ec) => ["idEc" => $ec->elementConstitutif->getId()],
                $ecArray
            )
        );
    }

    private function compareMcccs(?array $mcccsOriginal, ?array $mcccsNouveau): array
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


        return $diff;
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
}
