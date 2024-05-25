<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/GetDpeParcours.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 20/05/2024 14:26
 */

namespace App\Classes;

use App\Entity\DpeParcours;
use App\Entity\Formation;
use App\Entity\Parcours;

abstract class GetDpeParcours {

    public function __construct() {
    }

    public static function getFromParcours(Parcours $parcours): ?DpeParcours {
        return $parcours->getDpeParcours()->first(); //trié par ordre décroissant, le premier est donc le plus récent
    }

    public static function getFromFormation(?Formation $formation): ?DpeParcours
    {
        if (null !== $formation) {
            $parcours = $formation->getParcours();
            if (null !== $parcours) {
                return self::getFromParcours($parcours->first()); //todo: quel parcours prendre ?
            }
        }

        return null;
    }
}
