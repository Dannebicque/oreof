<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Enums/ModaliteEnseignementEnum.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Enums;

enum ModaliteEnseignementEnum: int
{
    case PRESENTIELLE = 0;
    case DISTANCIELLE = 2;
    case HYBRIDE = 1;

    public function libelle(): string
    {
        return match ($this) {
            self::PRESENTIELLE => 'Présentielle',
            self::DISTANCIELLE => 'Distancielle',
            self::HYBRIDE => 'Hybride',
        };
    }
}
