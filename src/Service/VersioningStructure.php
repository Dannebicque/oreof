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
use App\Utils\Tools;

class VersioningStructure
{
    public function __construct(
        private StructureParcours $dtoOrigine,
        private StructureParcours $dtoNouveau
    ) {
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

    private function compareSemestre(array $semestreOriginal, StructureSemestre $semestreNouveau)
    {
        $diff = [];

        $diff['raccroche'] = new DiffObject($semestreOriginal['raccroche'], $semestreNouveau->raccroche);
        $diff['ordre'] = new DiffObject($semestreOriginal['ordre'], $semestreNouveau->ordre);
        $diff['heuresEctsSemestre'] = $this->compareHeuresEctsSemestre($semestreOriginal['heuresEctsSemestre'], $semestreNouveau->heuresEctsSemestre);
        foreach ($semestreOriginal['ues'] as $ordreUe => $ue) {
            $diff['ues'][$ordreUe] = $this->compareUe($ue, $semestreNouveau->ues()[$ordreUe]);//cas si UE n'existe plus ou si ajouté dans nouveau ?
        }

        return $diff;
    }

    private function compareHeuresEctsSemestre(array $heuresEctsSemestre, HeuresEctsSemestre $heuresEctsSemestreNouveau): array
    {
        $diff = [];
        $diff['sommeSemestreEcts'] = new DiffObject($heuresEctsSemestre['sommeSemestreEcts'], $heuresEctsSemestreNouveau->sommeSemestreEcts);
        $diff['sommeSemestreCmPres'] = new DiffObject(Tools::filtreHeures($heuresEctsSemestre['sommeSemestreCmPres']), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreCmPres));
        $diff['sommeSemestreTdPres'] = new DiffObject(Tools::filtreHeures($heuresEctsSemestre['sommeSemestreTdPres']), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreTdPres));
        $diff['sommeSemestreTpPres'] = new DiffObject(Tools::filtreHeures($heuresEctsSemestre['sommeSemestreTpPres']), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreTpPres));
        $diff['sommeSemestreTePres'] = new DiffObject(Tools::filtreHeures($heuresEctsSemestre['sommeSemestreTePres']), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreTePres));
        $diff['sommeSemestreCmDist'] = new DiffObject(Tools::filtreHeures($heuresEctsSemestre['sommeSemestreCmDist']), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreCmDist));
        $diff['sommeSemestreTdDist'] = new DiffObject(Tools::filtreHeures($heuresEctsSemestre['sommeSemestreTdDist']), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreTdDist));
        $diff['sommeSemestreTpDist'] = new DiffObject(Tools::filtreHeures($heuresEctsSemestre['sommeSemestreTpDist']), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreTpDist));

        $heuresEctsSemestre['sommeSemestreTotalPres'] = $heuresEctsSemestre['sommeSemestreCmPres'] + $heuresEctsSemestre['sommeSemestreTdPres'] + $heuresEctsSemestre['sommeSemestreTpPres'];
        $diff['sommeSemestreTotalPres'] = new DiffObject(Tools::filtreHeures($heuresEctsSemestre['sommeSemestreTotalPres']), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreTotalPres()));

        $heuresEctsSemestre['sommeSemestreTotalDist'] = $heuresEctsSemestre['sommeSemestreCmDist'] + $heuresEctsSemestre['sommeSemestreTdDist'] + $heuresEctsSemestre['sommeSemestreTpDist'];
        $diff['sommeSemestreTotalDist'] = new DiffObject(Tools::filtreHeures($heuresEctsSemestre['sommeSemestreTotalDist']), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreTotalDist()));

        $heuresEctsSemestre['sommeSemestreTotalPresDist'] = $heuresEctsSemestre['sommeSemestreTotalPres'] + $heuresEctsSemestre['sommeSemestreTotalDist'];
        $diff['sommeSemestreTotalPresDist'] = new DiffObject(Tools::filtreHeures($heuresEctsSemestre['sommeSemestreTotalPresDist']), Tools::filtreHeures($heuresEctsSemestreNouveau->sommeSemestreTotalPresDist()));

        return $diff;
    }

    private function compareUe(array $ueOriginale, StructureUe $ueNouvelle): array
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

        $diff['display'] = new DiffObject($ueOriginale['display'], $ueNouvelle->display);
        $diff['raccroche'] = new DiffObject($ueOriginale['raccroche'], $ueNouvelle->raccroche);
        foreach ($ueOriginale['elementConstitutifs'] as $ordreEc => $ec) {
            $diff['elementConstitutifs'][$ordreEc] = $this->compareElementConstitutif($ec, $ueNouvelle->elementConstitutifs[$ordreEc]);
        }

        $diff['heuresEctsUe'] = $this->compareHeuresEctsUe($ueOriginale['heuresEctsUe'], $ueNouvelle->heuresEctsUe);
        //ue enfant ?

        return $diff;
    }

    private function compareElementConstitutif(mixed $ecOriginal, StructureEc $ecNouveau): array
    {
        $diff = [];
        //$diff['libelle'] = new DiffObject($ecOriginal['libelle'], $ecNouveau->elementConstitutif->getLibelle());
        $diff['raccroche'] = new DiffObject($ecOriginal['raccroche'], $ecNouveau->raccroche);
       // $diff['elementConstitutif'] = new DiffObject($ecOriginal['elementConstitutif'], $ecNouveau->elementConstitutif);
        $diff['heuresEctsEc'] = $this->compareHeuresEctsEc($ecOriginal['heuresEctsEc'], $ecNouveau->heuresEctsEc);

        return $diff;
    }

    private function compareHeuresEctsUe(array $heuresEctsUe, HeuresEctsUe $heuresEctsUe1)
    {
        $diff = [];
        $diff['sommeUeEcts'] = new DiffObject($heuresEctsUe['sommeUeEcts'], $heuresEctsUe1->sommeUeEcts);
        $diff['sommeUeCmPres'] = new DiffObject(Tools::filtreHeures($heuresEctsUe['sommeUeCmPres']), Tools::filtreHeures($heuresEctsUe1->sommeUeCmPres));
        $diff['sommeUeTdPres'] = new DiffObject(Tools::filtreHeures($heuresEctsUe['sommeUeTdPres']), Tools::filtreHeures($heuresEctsUe1->sommeUeTdPres));
        $diff['sommeUeTpPres'] = new DiffObject(Tools::filtreHeures($heuresEctsUe['sommeUeTpPres']), Tools::filtreHeures($heuresEctsUe1->sommeUeTpPres));
        $diff['sommeUeTePres'] = new DiffObject(Tools::filtreHeures($heuresEctsUe['sommeUeTePres']), Tools::filtreHeures($heuresEctsUe1->sommeUeTePres));
        $diff['sommeUeCmDist'] = new DiffObject(Tools::filtreHeures($heuresEctsUe['sommeUeCmDist']), Tools::filtreHeures($heuresEctsUe1->sommeUeCmDist));
        $diff['sommeUeTdDist'] = new DiffObject(Tools::filtreHeures($heuresEctsUe['sommeUeTdDist']), Tools::filtreHeures($heuresEctsUe1->sommeUeTdDist));

        $heuresEctsUe['sommeUeTotalPres'] = $heuresEctsUe['sommeUeCmPres'] + $heuresEctsUe['sommeUeTdPres'] + $heuresEctsUe['sommeUeTpPres'];
        $diff['sommeUeTotalPres'] = new DiffObject(Tools::filtreHeures($heuresEctsUe['sommeUeTotalPres']), Tools::filtreHeures($heuresEctsUe1->sommeUeTotalPres()));

        $heuresEctsUe['sommeUeTotalDist'] = $heuresEctsUe['sommeUeCmDist'] + $heuresEctsUe['sommeUeTdDist'] + $heuresEctsUe['sommeUeTpDist'];
        $diff['sommeUeTotalDist'] = new DiffObject(Tools::filtreHeures($heuresEctsUe['sommeUeTotalDist']), Tools::filtreHeures($heuresEctsUe1->sommeUeTotalDist()));

        $heuresEctsUe['sommeUeTotalPresDist'] = $heuresEctsUe['sommeUeTotalPres'] + $heuresEctsUe['sommeUeTotalDist'];
        $diff['sommeUeTotalPresDist'] = new DiffObject(Tools::filtreHeures($heuresEctsUe['sommeUeTotalPresDist']), Tools::filtreHeures($heuresEctsUe1->sommeUeTotalPresDist()));

        return $diff;
    }

    private function compareHeuresEctsEc(mixed $heuresEctsEc, HeuresEctsEc $heuresEctsEc1)
    {
        $diff = [];
        $diff['ects'] = new DiffObject($heuresEctsEc['ects'], $heuresEctsEc1->ects);
        $diff['cmPres'] = new DiffObject(Tools::filtreHeures($heuresEctsEc['cmPres']), Tools::filtreHeures($heuresEctsEc1->cmPres));
        $diff['tdPres'] = new DiffObject(Tools::filtreHeures($heuresEctsEc['tdPres']), Tools::filtreHeures($heuresEctsEc1->tdPres));
        $diff['tpPres'] = new DiffObject(Tools::filtreHeures($heuresEctsEc['tpPres']), Tools::filtreHeures($heuresEctsEc1->tpPres));
        $diff['tePres'] = new DiffObject(Tools::filtreHeures($heuresEctsEc['tePres']), Tools::filtreHeures($heuresEctsEc1->tePres));
        $diff['cmDist'] = new DiffObject(Tools::filtreHeures($heuresEctsEc['cmDist']), Tools::filtreHeures($heuresEctsEc1->cmDist));
        $diff['tdDist'] = new DiffObject(Tools::filtreHeures($heuresEctsEc['tdDist']), Tools::filtreHeures($heuresEctsEc1->tdDist));
        $diff['tpDist'] = new DiffObject(Tools::filtreHeures($heuresEctsEc['tpDist']), Tools::filtreHeures($heuresEctsEc1->tpDist));

        $heuresEctsEc['sommeEcTotalPres'] = $heuresEctsEc['cmPres'] + $heuresEctsEc['tdPres'] + $heuresEctsEc['tpPres'];
        $diff['sommeEcTotalPres'] = new DiffObject(Tools::filtreHeures($heuresEctsEc['sommeEcTotalPres']), Tools::filtreHeures($heuresEctsEc1->sommeEcTotalPres()));

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

    public function mapStructureForComparison(StructureParcours $dto, bool $isVersion){
        $structure = ['semestres' => []];
        // Semestres
        foreach($dto->semestres as $indexS => $semestre){
            $structure['semestres'][$indexS] = $isVersion 
                ? ['idSemestre' => $semestre['semestre']['id'], 'ues' => []] 
                : ['idSemestre' => $semestre->semestre->getId(), 'ues' => []];
            $ueArray = $isVersion ? $semestre['ues'] : $semestre->ues();
            $structure['semestres'][$indexS]['ues'] = $this->mapUeArrayForComparison($ueArray, $isVersion);
            foreach($ueArray as $indexUe => $ue){
                $ecArray = $isVersion ? $ue['elementConstitutifs'] : $ue->elementConstitutifs;
                $structure['semestres'][$indexS]['ues'][$indexUe]['listeEc'] = $this->mapEcArrayForComparison($ecArray, $isVersion);
                foreach($ecArray as $indexEc => $ec){
                    $ecEnfantData = $isVersion ? $ec['elementsConstitutifsEnfants'] : $ec->elementsConstitutifsEnfants;
                    $structure['semestres'][$indexS]['ues'][$indexUe]['listeEc'][$indexEc]['listeEcsEnfants'] = $this->mapEcArrayForComparison($ecEnfantData, $isVersion);
                }
                $ueEnfantArrayData = $isVersion ? $ue['uesEnfants'] : $ue->uesEnfants();
                $structure['semestres'][$indexS]['ues'][$indexUe]['uesEnfants'] = $this->mapUeArrayForComparison($ueEnfantArrayData, $isVersion);
                foreach($ueEnfantArrayData as $indexUeEnfant => $ueEnfant){
                    $ecArrayData = $isVersion ? $ueEnfant['elementConstitutifs'] : $ueEnfant->elementConstitutifs;
                    $structure['semestres'][$indexS]['ues'][$indexUe]['uesEnfants'][$indexUeEnfant]['listeEc'] = $this->mapEcArrayForComparison($ecArrayData, $isVersion);
                    foreach($ecArrayData as $indexEcEnfantUeEnfant => $ec){
                        $ecEnfantData = $isVersion ? $ec['elementsConstitutifsEnfants'] : $ec->elementsConstitutifsEnfants;
                        $structure['semestres'][$indexS]['ues'][$indexUe]['uesEnfants'][$indexUeEnfant]['listeEc'][$indexEcEnfantUeEnfant]['listeEcsEnfants'] = $this->mapEcArrayForComparison($ecEnfantData, $isVersion);
                    }   
                } 
            }
        }

        return $structure;
    }

    public function mapUeArrayForComparison(array $ueArray, bool $isVersion){
        return $isVersion 
        ? array_values(array_map(
            fn($ue) => ["idUe" => $ue['ue']['id']],
                $ueArray)
            )
        : array_values(array_map(
            fn($ue) => ["idUe" => $ue->ue->getId()],
                $ueArray)
        );
    }

    public function mapEcArrayForComparison(array $ecArray, bool $isVersion){
        return $isVersion
        ? array_values(array_map(
            fn($ec) => ["idEc" => $ec['elementConstitutif']['id']],
                $ecArray)
        )
        : array_values(array_map(
            fn($ec) => ["idEc" => $ec->elementConstitutif->getId()],
                $ecArray
            )
        );
    }
}
