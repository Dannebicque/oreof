<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Utils/Access.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 23/11/2024 10:35
 */

namespace App\Utils;

use App\Entity\DpeParcours;
use App\Entity\Formation;
use App\Enums\TypeModificationDpeEnum;

abstract class Access {
    public static function isAccessible(DpeParcours $dpeParcours, string $state = 'cfvu'): bool
    {
        //todo: intégrer le isGranted ici ou dans les vues ?
        if ($state === 'cfvu') {
            return $dpeParcours->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_MCCC || $dpeParcours->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE;
        }

        return $dpeParcours->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_TEXTE;
    }

    public static function isAccessibleMention(Formation $formation, string $state): bool
    {
        if ($state === 'cfvu') {
            return $formation->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION ||
                $formation->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_MCCC_TEXTE ||
                $formation->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_MCCC ||
                $formation->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_PARCOURS;
        }

        return $formation->getEtatReconduction() === TypeModificationDpeEnum::MODIFICATION_TEXTE;
    }
}
