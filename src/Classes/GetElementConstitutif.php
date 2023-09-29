<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/GetElementConstitutif.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 24/09/2023 16:09
 */

namespace App\Classes;

use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use App\TypeDiplome\Source\TypeDiplomeInterface;

abstract class GetElementConstitutif
{
    public static function getElementConstitutif(ElementConstitutif $elementConstitutif, bool $raccroche): ElementConstitutif|FicheMatiere
    {
        if ($raccroche && $elementConstitutif->getFicheMatiere() !== null && $elementConstitutif->getFicheMatiere()?->getParcours() !== null) {
            foreach ($elementConstitutif->getFicheMatiere()?->getParcours()?->getElementConstitutifs() as $ec) {
                if ($ec->getFicheMatiere()?->getId() === $elementConstitutif->getFicheMatiere()?->getId()) {
                    return $ec;
                }
            }
        } elseif ($elementConstitutif->getEcParent() !== null) {
            return $elementConstitutif->getEcParent();
        }
        return $elementConstitutif;
    }

    public static function getMcccs(ElementConstitutif $elementConstitutif, bool $raccroche, TypeDiplomeInterface $typeD): array
    {
        if ($elementConstitutif->getFicheMatiere()?->isMcccImpose()) {
            return $typeD->getMcccs($elementConstitutif->getFicheMatiere());
        }

        if ($elementConstitutif->isSynchroMccc() === true) {
            return $typeD->getMcccs(self::getElementConstitutif($elementConstitutif, $raccroche));
        }
        //$ec = self::getElementConstitutif($elementConstitutif, $raccroche);

        return $typeD->getMcccs($elementConstitutif);
    }

    public static function getEcts(ElementConstitutif $elementConstitutif, bool $raccroche): ?float
    {
        if ($elementConstitutif->getFicheMatiere()?->isEctsImpose()) {
            return $elementConstitutif->getFicheMatiere()?->getEcts();
        }

        if ($elementConstitutif->isSynchroEcts() === true) {
            return self::getElementConstitutif($elementConstitutif, $raccroche)?->getEcts();
        }

        if ($elementConstitutif->getEcParent() !== null) {
            return $elementConstitutif->getEcParent()->getEcts();
        }

        return $elementConstitutif->getEcts();
    }

    public static function getElementConstitutifHeures(ElementConstitutif $elementConstitutif, bool $raccroche): ElementConstitutif|FicheMatiere
    {
        if ($elementConstitutif->getFicheMatiere()?->isVolumesHorairesImpose()) {
            return $elementConstitutif->getFicheMatiere();
        }

        if ($elementConstitutif->isSynchroHeures() === true) {
            return self::getElementConstitutif($elementConstitutif, $raccroche);
        }
        return $elementConstitutif;
    }

    public static function isRaccroche(ElementConstitutif $elementConstitutif, Parcours $parcours)
    {
        if ($elementConstitutif->getFicheMatiere()?->isHorsDiplome()) {
            return true;
        }

        if ($elementConstitutif->getFicheMatiere()?->getParcours() === null) {
            return false;
        }

        return $elementConstitutif->getFicheMatiere()?->getParcours() !== $parcours;
    }
}
