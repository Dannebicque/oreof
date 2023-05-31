<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Enums/PermissionEnum.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Enums;

enum PorteeEnum: string
{
    case ALL = 'ALL';
    case MY = 'MY';

    public static function getAvailableTypes()
    {
        return [
            self::ALL->value,
            self::MY->value
        ];
    }

    public function libelle(): string
    {
        return match ($this) {
            self::ALL => 'Toutes',
            self::MY => 'Uniquement sur mon périmètre'
        };
    }
}
