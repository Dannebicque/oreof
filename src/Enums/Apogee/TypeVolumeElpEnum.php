<?php
/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Enums/Apogee/TypeVolumeElpEnum.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/01/2024 19:30
 */

namespace App\Enums\Apogee;

enum TypeVolumeElpEnum: string
{
    //‘HE’ = Heures - ‘ST’ = Semestres - ‘SM’ = Semaines - ‘AN’ = Années - ‘MO’ = Mois - ‘PE’ = Périodes - ‘TR’ = Trimestres

    case HE = 'HE';
    case ST = 'ST';
    case SM = 'SM';
    case AN = 'AN';
    case MO = 'MO';
    case PE = 'PE';
    case TR = 'TR';
}
