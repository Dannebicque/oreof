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
use Doctrine\Common\Collections\Collection;

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

        if ($elementConstitutif->isSynchroMccc() === true && $raccroche === true) {
            return $typeD->getMcccs(self::getElementConstitutif($elementConstitutif, $raccroche));
        }
        //$ec = self::getElementConstitutif($elementConstitutif, $raccroche);
        return $typeD->getMcccs($elementConstitutif);
    }

    public static function getMcccsCollection(ElementConstitutif $elementConstitutif, bool $raccroche): ?Collection
    {
        if ($elementConstitutif->getFicheMatiere()?->isMcccImpose()) {
            return $elementConstitutif->getFicheMatiere()->getMcccs();
        }

        if ($elementConstitutif->isSynchroMccc() === true && $raccroche === true) {
            return self::getElementConstitutif($elementConstitutif, $raccroche)->getMcccs();
        }

        return $elementConstitutif->getMcccs();
    }

    public static function getEcts(ElementConstitutif $elementConstitutif, bool $raccroche): ?float
    {
        if ($elementConstitutif->getFicheMatiere()?->isEctsImpose()) {
            return $elementConstitutif->getFicheMatiere()?->getEcts();
        }

        if ($elementConstitutif->isSynchroEcts() === true && $raccroche === true) {
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

        if ($elementConstitutif->getEcParent() !== null && $elementConstitutif->getEcParent()->isHeuresEnfantsIdentiques() === true) {
            return $elementConstitutif->getEcParent();
        }

        if ($elementConstitutif->isSynchroHeures() === true && $raccroche === true) {
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

    public static function getEtatsMccc(ElementConstitutif $elementConstitutif, bool $raccroche): ?string
    {
        if ($elementConstitutif->getEcParent() !== null && $elementConstitutif->getEcParent()->isMcccEnfantsIdentique() === true) {
            return $elementConstitutif->getEcParent()->getEtatMccc();
        }

        if ($elementConstitutif->getFicheMatiere()?->isMcccImpose()) {
            return $elementConstitutif->getFicheMatiere()?->getEtatMccc();
        }

        if ($elementConstitutif->isSynchroMccc() === true && $raccroche === true) {
            return self::getElementConstitutif($elementConstitutif, $raccroche)->getEtatMccc();
        }

        return $elementConstitutif->getEtatMccc();
    }

    public static function getEtatStructure(ElementConstitutif $elementConstitutif, bool $raccroche): ?string
    {
        if ($elementConstitutif->getFicheMatiere()?->isVolumesHorairesImpose()) {
            return $elementConstitutif->getFicheMatiere()?->etatStructure();
        }

        if ($elementConstitutif->getEcParent() !== null && $elementConstitutif->getEcParent()->isHeuresEnfantsIdentiques() === true) {
            return $elementConstitutif->getEcParent()->etatStructure();
        }

        if ($elementConstitutif->isSynchroHeures() === true && $raccroche === true) {
            return self::getElementConstitutif($elementConstitutif, $raccroche)->etatStructure();
        }
        return $elementConstitutif->etatStructure();
    }

    public static function getEtatBcc(ElementConstitutif $elementConstitutif, bool $raccroche): ?string
    {
        // cas du BUT
        if ($elementConstitutif->getFicheMatiere()?->getApprentissagesCritiques()->count() > 0) {
            //todo: les Ac doivent être dans la bonne compétence...
            foreach ($elementConstitutif->getFicheMatiere()?->getApprentissagesCritiques() as $ac) {
                if ($ac->getNiveau() !== null &&
                    $ac->getNiveau()->getCompetence()?->getNumero() === $elementConstitutif->getUe()?->getOrdre()) {
                    return 'Complet';
                }
            }
        }


        if ($raccroche === true && $elementConstitutif->isSynchroBcc() === true) {
            $ec = self::getElementConstitutif($elementConstitutif, $raccroche);
            if ($ec->getCompetences()->count() === 0) {
                return $ec->getFicheMatiere()?->getCompetences()->count() > 0 ? 'Complet' : 'A saisir';
            }
            return $ec->getCompetences()->count() > 0 ? 'Complet' : 'A saisir';
        }

        if ($elementConstitutif->getCompetences()->count() === 0) {
            return $elementConstitutif->getFicheMatiere()?->getCompetences()->count() > 0 ? 'Complet' : 'A saisir';
        }

        return $elementConstitutif->getCompetences()->count() > 0 ? 'Complet' : 'A saisir';

    }

    public static function getTypeMccc(ElementConstitutif $elementConstitutif, bool $raccroche): ?string
    {
        if ($elementConstitutif->getEcParent() !== null && $elementConstitutif->getEcParent()->isMcccEnfantsIdentique() === true) {
            return $elementConstitutif->getEcParent()->getTypeMccc();
        }

        if ($elementConstitutif->getFicheMatiere()?->isMcccImpose()) {
            return $elementConstitutif->getFicheMatiere()?->getTypeMccc();
        }

        if ($elementConstitutif->isSynchroMccc() === true && $raccroche === true) {
            return self::getElementConstitutif($elementConstitutif, $raccroche)->getTypeMccc();
        }

        return $elementConstitutif->getTypeMccc();
    }

    public static function getBccs(ElementConstitutif $elementConstitutif, bool $raccroche): ?Collection
    {
        if ($raccroche === true && $elementConstitutif->isSynchroBcc() === true) {
            $ec = self::getElementConstitutif($elementConstitutif, $raccroche);
            if ($ec->getCompetences()->count() === 0) {
                return $ec->getFicheMatiere()?->getCompetences();
            }
            return $ec->getCompetences();
        }

        if ($elementConstitutif->getCompetences()->count() === 0) {
            return $elementConstitutif->getFicheMatiere()?->getCompetences();
        }

        return $elementConstitutif->getCompetences();
    }
}
