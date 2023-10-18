<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/GetUeEcts.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 18/10/2023 19:04
 */

namespace App\Classes;

use App\Entity\Parcours;
use App\Entity\TypeDiplome;
use App\Entity\Ue;

abstract class GetUeEcts
{
    public static function getEcts(Ue $ue, Parcours $parcours, TypeDiplome $typeDiplome): float {
        if ($typeDiplome->getLibelleCourt() === 'BUT') {
            return $ue->getEcts();
        }

        if ($ue->getNatureUeEc()?->isLibre()) {
            return $ue->getEcts();
        }

        if ($ue->getUeEnfants()->count() === 0) {
            return self::totalEctsUe($ue, $parcours);
        }

        $tEcts = [];
        foreach ($ue->getUeEnfants() as $ueEnfant) {
            $tEcts[] = self::totalEctsUe($ueEnfant, $parcours);
        }
        return min($tEcts) ?? 0.0;
    }

    private static function totalEcts($ue, $parcours): float {
        $ecsInUe = $ue->getElementConstitutifs();
        $totalEctsUe = 0.0;
        foreach ($ecsInUe as $ec) {
            if ($ec->getEcParent() === null) {
                $raccroche = $ec->getFicheMatiere()?->getParcours()?->getId() !== $parcours->getId();
                if ($raccroche && $ec->isSynchroEcts()) {

                    $ects = GetElementConstitutif::getEcts($ec, $raccroche);
                    $totalEctsUe += $ects;
                } else {
                    $totalEctsUe += $ec->getEcts();
                }
            }
        }

        return $totalEctsUe;
    }

    private static function totalEctsUe(Ue $ue, $parcours): float
    {
        if ($ue->getUeRaccrochee() !== null) {
            if ($ue->getUeRaccrochee()->getUe() !== null) {
                $ects = self::totalEcts($ue->getUeRaccrochee()->getUe(), $parcours);
            } else {
                $ects = 'Erreur UE raccrochée';
            }
        } else {
            $ects = self::totalEcts($ue, $parcours);
        }

        return $ects;
    }
}
