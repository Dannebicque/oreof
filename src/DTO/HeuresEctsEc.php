<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DTO/HeuresEctsUe.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/07/2023 17:35
 */

namespace App\DTO;

use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;

use Symfony\Component\Serializer\Annotation\Groups;

class HeuresEctsEc
{
    /** @var float $ects */
    #[Groups(['DTO_json_versioning'])]
    public float $ects = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $cmPres = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $tdPres = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $tpPres = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $tePres = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $cmDist = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $tdDist = 0;

    #[Groups(['DTO_json_versioning'])]
    public float $tpDist = 0;

    public function sommeEcTotalDist(): float
    {
        return $this->cmDist + $this->tdDist + $this->tpDist;
    }

    public function sommeEcTotalPres(): float
    {
        return $this->cmPres + $this->tdPres + $this->tpPres;
    }

    public function sommeEcTotalPresDist(): float
    {
        return $this->sommeEcTotalPres() + $this->sommeEcTotalDist();
    }

    public function addEcts(?float $ects = 0.0): void
    {
        $this->ects = $ects ?? 0.0;
    }

    public function addEc(ElementConstitutif|FicheMatiere $elementConstitutif, bool $isBut = false): void
    {
        //ne devrait pas être un test sur but ici, sinon non générique
        if ($isBut) {
            if ($elementConstitutif instanceof FicheMatiere) {
                $this->cmPres = $elementConstitutif->getVolumeCmPresentiel() ?? 0.0;
                $this->tdPres = $elementConstitutif->getVolumeTdPresentiel() ?? 0.0;
                $this->tpPres = $elementConstitutif->getVolumeTpPresentiel() ?? 0.0;
                $this->tePres = $elementConstitutif->getVolumeTe() ?? 0.0;
                $this->cmDist = $elementConstitutif->getVolumeCmDistanciel() ?? 0.0;
                $this->tdDist = $elementConstitutif->getVolumeTdDistanciel() ?? 0.0;
                $this->tpDist = $elementConstitutif->getVolumeTpDistanciel() ?? 0.0;
            } else {
                $ficheMatiere = $elementConstitutif->getFicheMatiere();
                if ($ficheMatiere !== null) {
                    $this->cmPres = $ficheMatiere->getVolumeCmPresentiel() ?? 0.0;
                    $this->tdPres = $ficheMatiere->getVolumeTdPresentiel() ?? 0.0;
                    $this->tpPres = $ficheMatiere->getVolumeTpPresentiel() ?? 0.0;
                    $this->tePres = $ficheMatiere->getVolumeTe() ?? 0.0;
                    $this->cmDist = $ficheMatiere->getVolumeCmDistanciel() ?? 0.0;
                    $this->tdDist = $ficheMatiere->getVolumeTdDistanciel() ?? 0.0;
                    $this->tpDist = $ficheMatiere->getVolumeTpDistanciel() ?? 0.0;
                }
            }
        } else {
            $this->cmPres = $elementConstitutif->getVolumeCmPresentiel() ?? 0.0;
            $this->tdPres = $elementConstitutif->getVolumeTdPresentiel() ?? 0.0;
            $this->tpPres = $elementConstitutif->getVolumeTpPresentiel() ?? 0.0;
            $this->tePres = $elementConstitutif->getVolumeTe() ?? 0.0;
            $this->cmDist = $elementConstitutif->getVolumeCmDistanciel() ?? 0.0;
            $this->tdDist = $elementConstitutif->getVolumeTdDistanciel() ?? 0.0;
            $this->tpDist = $elementConstitutif->getVolumeTpDistanciel() ?? 0.0;
        }
    }
}
