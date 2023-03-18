<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Enums/CentreGestionEnum.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Enums;

enum CentreGestionEnum: string
{
    case CENTRE_GESTION_ETABLISSEMENT = 'cg_etablissement';
    case CENTRE_GESTION_COMPOSANTE = 'cg_composante';
    case CENTRE_GESTION_FORMATION = 'cg_formation';

    public function libelle(): string
    {
        return match ($this) {
            self::CENTRE_GESTION_ETABLISSEMENT => 'Etablissement',
            self::CENTRE_GESTION_COMPOSANTE => 'Composante',
            self::CENTRE_GESTION_FORMATION => 'Formation',
        };
    }
}
