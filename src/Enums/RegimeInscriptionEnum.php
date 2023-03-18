<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Enums/RegimeInscriptionEnum.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 25/01/2023 17:28
 */

namespace App\Enums;

enum RegimeInscriptionEnum: string
{
    case FI = 'Formation Initiale';
    case FI_APPRENTISSAGE = 'Formation Initiale en apprentissage';
    case FC = 'Formation Continue';
    case FC_CONTRAT_PRO = 'Formation Continue Contrat Professionnalisation';
}
