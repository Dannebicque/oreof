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
use App\Entity\TypeDiplome;
use App\TypeDiplome\Source\TypeDiplomeInterface;
use ContainerJaU2YpB\getElementConstitutif2Service;

abstract class GetElementConstitutif
{
    public static function getElementConstitutif(ElementConstitutif $elementConstitutif, bool $raccroche)
    {


        if ($raccroche && $elementConstitutif->isSynchroMccc()) {
            foreach ($elementConstitutif->getFicheMatiere()->getParcours()->getElementConstitutifs() as $ec) {
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

        $ec = self::getElementConstitutif($elementConstitutif, $raccroche);

        return $typeD->getMcccs($ec);
    }

    public static function getEcts(ElementConstitutif $elementConstitutif, bool $raccroche): ?float
    {
        if ($elementConstitutif->getFicheMatiere()?->isEctsImpose()) {
            return $elementConstitutif->getFicheMatiere()->getEcts();
        }

        return self::getElementConstitutif($elementConstitutif, $raccroche)->getEcts();
    }

    public static function getElementConstitutifHeures(ElementConstitutif $elementConstitutif, bool $raccroche): ElementConstitutif|FicheMatiere
    {
        if ($elementConstitutif->getFicheMatiere()?->isVolumesHorairesImpose()) {
            return $elementConstitutif->getFicheMatiere();
        }

        return self::getElementConstitutif($elementConstitutif, $raccroche);
    }
}
