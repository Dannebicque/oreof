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
    public static function getEcts(Ue $ue, Parcours $parcours, TypeDiplome $typeDiplome): ?float
    {
        if ($typeDiplome->getLibelleCourt() === 'BUT') {
            return $ue->getEcts() ?? 0.0;
        }

        if ($ue->getUeEnfants()->count() === 0) {
            return self::totalEctsUe($ue, $parcours) ?? 0.0;
        }

        $tEcts = [];
        foreach ($ue->getUeEnfants() as $ueEnfant) {
            $tEcts[] = self::totalEctsUe($ueEnfant, $parcours);
        }
        return min($tEcts) ?? 0.0;
    }

    private static function totalEcts(Ue $ue, Parcours $parcours): ?float
    {
        if ($ue->getNatureUeEc()?->isLibre()) {
            return $ue->getEcts();
        }

        $ecsInUe = $ue->getElementConstitutifs();
        $totalEctsUe = 0.0;
        foreach ($ecsInUe as $ec) {
            if ($ec->getEcParent() === null) {
                $raccroche = $ec->getFicheMatiere()?->getParcours()?->getId() !== $parcours->getId();
                if ($ec->getFicheMatiere() !== null && $ec->getFicheMatiere()?->isEctsImpose()) {
                    $totalEctsUe += $ec->getFicheMatiere()?->getEcts();
                } elseif ($raccroche && $ec->isSynchroEcts()) {
                    $ects = GetElementConstitutif::getEcts($ec, $raccroche);
                    $totalEctsUe += $ects;
                } else {
                    $totalEctsUe += $ec->getEcts();
                }
            }
        }

        return $totalEctsUe;
    }

    private static function totalEctsUe(Ue $ue, Parcours $parcours): ?float
    {
        if ($ue->getUeRaccrochee() !== null) {
            if ($ue->getUeRaccrochee()->getUe() !== null) {
                $ue = $ue->getUeRaccrochee()->getUe();
            }
        }

        return self::totalEcts($ue, $parcours);
    }
}
