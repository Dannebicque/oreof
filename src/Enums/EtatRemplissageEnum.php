<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Enums/EtatRemplissageEnum.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Enums;

enum EtatRemplissageEnum: string
{
    case EN_COURS = 'EN_COURS';
    case VIDE = 'VIDE';
    case COMPLETE = 'COMPLETE';

    public function badge(): string
    {
        return match ($this) {
            self::EN_COURS => 'en-cours',
            self::VIDE => 'vide',
            self::COMPLETE => 'complete',
        };
    }
}
